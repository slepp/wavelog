/*
 * Live logbook: refresh the QSO table (and map, if shown) when QSOs change.
 * Uses the Wavelog Worker websocket when available (falling back to polling
 * if the socket gives up), otherwise polls periodically. Only active on
 * page 1 of the logbook (the view omits data-live-enabled on deeper pages).
 */
document.addEventListener('DOMContentLoaded', function () {
	const container = document.getElementById('logbook-table-container');
	if (!container || container.dataset.liveEnabled === undefined) return;

	// One refresh at most every LOCKOUT_MS after the previous response; the
	// 30s poll matches the QSO page's fallback idiom.
	const LOCKOUT_MS = 4000;
	const POLL_MS = 30000;
	const liveTableUrl = base_url + 'index.php/logbook/live_table';
	const savePrefUrl = base_url + 'index.php/user_options/save_logbook_pref';
	const toggle = document.getElementById('live_logbook_toggle');

	let liveEnabled = container.dataset.liveEnabled === '1';
	let workerLive = false; // true while the websocket subscription is believed healthy
	let hiddenMissed = false; // a push arrived while the tab was hidden
	let pollTimer = null;

	function refreshLogbook() {
		return wlLoadInto(liveTableUrl, container, { skipUnchanged: true }).then(function (swapped) {
			if (swapped) {
				wlBindQsoActionsMenu();
			}
			// leafembed globals only exist when the logbook map is enabled
			if (typeof askForPlots === 'function' && typeof qso_loc !== 'undefined' && document.getElementById('map')) {
				askForPlots(qso_loc, { dataPost: { nb_qso: container.dataset.perPage, offset: '' }, map_id: '#map' });
			}
		});
	}

	// Serialized trailing-edge throttle: never more than one request in flight,
	// and the lockout starts when the response lands so slow responses cannot
	// pile up or apply out of order. Pushes during the lockout collapse into a
	// single trailing refresh so no update is lost. Hidden tabs defer the
	// refresh until the tab is visible again.
	var pending = false;
	var missed = false;
	function throttledRefresh() {
		if (!liveEnabled) return;
		if (document.hidden) {
			hiddenMissed = true;
			return;
		}
		if (pending) {
			missed = true;
			return;
		}
		pending = true;
		refreshLogbook().catch(function () {}).then(function () {
			setTimeout(function () {
				pending = false;
				if (missed) {
					missed = false;
					throttledRefresh();
				}
			}, LOCKOUT_MS);
		});
	}

	function applyState() {
		const needPolling = liveEnabled && !workerLive && !document.hidden;
		if (needPolling && !pollTimer) {
			pollTimer = setInterval(throttledRefresh, POLL_MS);
		} else if (!needPolling && pollTimer) {
			clearInterval(pollTimer);
			pollTimer = null;
		}
		if (toggle) {
			toggle.classList.toggle('btn-outline-success', liveEnabled);
			toggle.classList.toggle('btn-outline-secondary', !liveEnabled);
		}
	}

	function setLive(on) {
		liveEnabled = on;
		applyState();
	}

	if (container.dataset.workerTopic && window.WavelogWorker && WavelogWorker.isAvailable()) {
		workerLive = true;
		WavelogWorker.subscribe({
			topic: container.dataset.workerTopic,
			token: container.dataset.workerToken,
			onMessage: function (frame) {
				if (frame.type === 'push' && frame.payload && frame.payload.type === 'qso_changed') {
					throttledRefresh();
				}
			},
			onReconnect: function () {
				throttledRefresh();
			},
			onFailed: function () {
				// the websocket gave up (worker down/unreachable): fall back to polling
				workerLive = false;
				applyState();
			}
		});
	}

	// don't poll or refresh in background tabs; catch up once visible again
	// (poll mode always needs a catch-up since the timer was paused; in worker
	// mode only when a push arrived while hidden)
	document.addEventListener('visibilitychange', function () {
		applyState();
		if (!document.hidden && (hiddenMissed || !workerLive)) {
			hiddenMissed = false;
			throttledRefresh();
		}
	});

	if (toggle) {
		toggle.addEventListener('click', function () {
			const wanted = !liveEnabled;
			setLive(wanted);
			fetch(savePrefUrl, {
				method: 'POST',
				headers: { 'X-Requested-With': 'XMLHttpRequest' },
				body: JSON.stringify({ value: wanted ? '1' : '0' })
			})
				.then(r => (r.ok && !r.redirected) ? r.json() : Promise.reject())
				.then(j => { if (!j.success) return Promise.reject(); })
				.catch(function () {
					// preference not saved (e.g. insufficient privileges): revert
					setLive(!wanted);
				});
			if (wanted) throttledRefresh();
		});
	}
	applyState();
});
