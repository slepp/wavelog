/*
 * Live logbook: refresh the QSO table (and map, if shown) when QSOs change.
 * Uses the Wavelog Worker websocket when available (falling back to polling
 * if the socket gives up), otherwise polls periodically. Only active on
 * page 1 of the logbook (the view omits data-live-url on deeper pages).
 */
document.addEventListener('DOMContentLoaded', function () {
	const container = document.getElementById('logbook-table-container');
	if (!container || !container.dataset.liveUrl) return;

	// One refresh at most every LOCKOUT_MS after the previous response; the
	// 30s poll matches the QSO page's fallback idiom.
	const LOCKOUT_MS = 4000;
	const POLL_MS = 30000;

	let liveEnabled = container.dataset.liveEnabled === '1';
	let workerLive = false; // true while the websocket subscription is believed healthy
	let pollTimer = null;

	// The QSO actions hover menu is bound directly (not delegated), so it must be
	// rebound after every table swap — same pattern as callstats.js/activators.js.
	function bindQsoActionsMenu() {
		$('.table-responsive .dropdown-toggle').off('mouseenter').on('mouseenter', function () {
			showQsoActionsMenu($(this).closest('.dropdown'));
		});
	}

	function refreshLogbook() {
		return wlLoadInto(container.dataset.liveUrl, container).then(function () {
			bindQsoActionsMenu();
			// leafembed globals only exist when the logbook map is enabled;
			// nb_qso mirrors per_page in Logbook::log_pagination_config()
			if (typeof askForPlots === 'function' && typeof qso_loc !== 'undefined' && document.getElementById('map')) {
				askForPlots(qso_loc, { dataPost: { nb_qso: '25', offset: '' }, map_id: '#map' });
			}
		});
	}

	// Serialized trailing-edge throttle: never more than one request in flight,
	// and the lockout starts when the response lands so slow responses cannot
	// pile up or apply out of order. Pushes during the lockout collapse into a
	// single trailing refresh so no update is lost.
	var pending = false;
	var missed = false;
	function throttledRefresh() {
		if (!liveEnabled) return;
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
		const toggle = document.getElementById('live_logbook_toggle');
		if (toggle) {
			toggle.classList.toggle('btn-outline-success', liveEnabled);
			toggle.classList.toggle('btn-outline-secondary', !liveEnabled);
			const dot = toggle.querySelector('i');
			if (dot) {
				dot.classList.toggle('text-success', liveEnabled);
				dot.classList.toggle('text-muted', !liveEnabled);
			}
		}
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

	// don't poll in background tabs; catch up once the tab is visible again
	document.addEventListener('visibilitychange', function () {
		applyState();
		if (!document.hidden) throttledRefresh();
	});

	const toggle = document.getElementById('live_logbook_toggle');
	if (toggle) {
		toggle.addEventListener('click', function () {
			const wanted = !liveEnabled;
			liveEnabled = wanted;
			applyState();
			fetch(container.dataset.savePrefUrl, {
				method: 'POST',
				headers: { 'X-Requested-With': 'XMLHttpRequest' },
				body: JSON.stringify({ value: wanted ? '1' : '0' })
			})
				.then(r => r.ok ? r.json() : Promise.reject())
				.then(j => { if (!j.success) return Promise.reject(); })
				.catch(function () {
					// preference not saved (e.g. insufficient privileges): revert
					liveEnabled = !wanted;
					applyState();
				});
			if (wanted) throttledRefresh();
		});
	}
	applyState();
});
