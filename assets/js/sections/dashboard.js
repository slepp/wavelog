
$(document).ready(function () {
    if ($('#station_dxcc').length) {
        $('#station_dxcc').multiselect({
            // template is needed for bs5 support
            templates: {
                button: '<button type="button" style="text-align: left !important;" class="multiselect dropdown-toggle btn btn-secondary w-auto" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false"><span class="multiselect-selected-text"></span></button>',
            },
            enableFiltering: true,
            enableFullValueFiltering: false,
            enableCaseInsensitiveFiltering: true,
            filterPlaceholder: lang_general_word_search,
            widthSynchronizationMode: 'always',
            numberDisplayed: 1,
            inheritClass: true,
            buttonWidth: '100%',
            maxHeight: 300,
        });
        $('.multiselect-container .multiselect-filter', $('#station_dxcc').parent()).css({
            'position': 'sticky', 'top': '0px', 'z-index': 1, 'background-color': 'inherit', 'width': '100%', 'height': '37px'
        });
    }

    const loadRadio = () => wlLoadInto(base_url + 'index.php/dashboard/radio_display_component', '#radio_display');
	loadRadio();

    var radioPending = null;
	var radioMissed = false;
	function throttleLoadRadio() {
		// Load at most once every 1 seconds. If more pushes arrive during the
		// lockout, refresh once afterwards so no update is lost.
		if (radioPending) {
			radioMissed = true;
			return;
		}
		loadRadio();
		radioPending = setTimeout(function () {
			radioPending = null;
			if (radioMissed) {
				radioMissed = false;
				throttleLoadRadio();
			}
		}, 1000);
	}

	var rw = window.radiosUserWorker;
	if (rw && window.WavelogWorker && WavelogWorker.isAvailable()) {
		setInterval(loadRadio, 60000);
		WavelogWorker.subscribe({
			topic: rw.topic,
			token: rw.token,
			onMessage: function (frame) {
				if (frame.type !== 'push' || !frame.payload || frame.payload.type !== 'radio_updated' || !frame.payload.radio_status) {
					return;
				}
				var cell = $('#radio_display tr[data-radio-id="' + frame.payload.radio_id + '"] .radio-qrg');
				if (!cell.length) {
					throttleLoadRadio();
					return;
				}
				var s = frame.payload.radio_status;
				if (s.prop_mode === 'SAT') {
					cell.text(s.satname || '');
				} else {
					cell.text((s.frequency_formatted || '') + ' (' + (s.mode || '') + ')');
				}
			},
			onReconnect: function () { throttleLoadRadio(); }
		});
	} else {
		setInterval(loadRadio, 5000);
	}

	const recentQsos = document.getElementById('dashboard-recent-qsos');
	if (!recentQsos) return;

	const refreshDashboard = function () {
		const recent = wlLoadInto(base_url + 'index.php/dashboard/live_recent_qsos', recentQsos, { skipUnchanged: true });
		const summary = fetch(base_url + 'index.php/dashboard/live_summary', {
			headers: { 'X-Requested-With': 'XMLHttpRequest' }
		})
			.then(function (response) {
				return response.ok ? response.json() : null;
			})
			.then(function (data) {
				if (!data) return;
				Object.entries({
					'dashboard-total-qsos': data.total_qsos,
					'dashboard-year-qsos': data.year_qsos,
					'dashboard-month-qsos': data.month_qsos,
					'dashboard-todays-qsos': data.todays_qsos,
					'dashboard-current-streak': data.current_streak,
					'dashboard-unique-callsigns': data.unique_callsigns,
					'dashboard-countries-worked': data.countries_worked,
					'dashboard-countries-confirmed-qsl': data.countries_confirmed_qsl,
					'dashboard-countries-confirmed-lotw': data.countries_confirmed_lotw,
					'dashboard-countries-confirmed-eqsl': data.countries_confirmed_eqsl,
					'dashboard-countries-needed': data.countries_needed
				}).forEach(function (entry) {
					const element = document.getElementById(entry[0]);
					if (element && element.textContent !== String(entry[1])) {
						element.textContent = entry[1];
					}
				});
			});

		return Promise.all([recent, summary]).then(function () {
			if (typeof askForPlots === 'function' && typeof qso_loc !== 'undefined' && document.getElementById('map')) {
				askForPlots(qso_loc, { map_id: '#map' });
			}
		});
	};

	let qsoPending = false;
	let qsoMissed = false;
	let qsoWorkerLive = false;
	let qsoPollTimer = null;
	let qsoHiddenMissed = false;

	function applyQsoPolling() {
		const shouldPoll = !qsoWorkerLive && !document.hidden;
		if (shouldPoll && !qsoPollTimer) {
			qsoPollTimer = setInterval(throttledQsoRefresh, 30000);
		} else if (!shouldPoll && qsoPollTimer) {
			clearInterval(qsoPollTimer);
			qsoPollTimer = null;
		}
	}

	function throttledQsoRefresh() {
		if (document.hidden) {
			qsoHiddenMissed = true;
			return;
		}
		if (qsoPending) {
			qsoMissed = true;
			return;
		}
		qsoPending = true;
		refreshDashboard().catch(function () {}).then(function () {
			setTimeout(function () {
				qsoPending = false;
				if (qsoMissed) {
					qsoMissed = false;
					throttledQsoRefresh();
				}
			}, 4000);
		});
	}

	const qsoWorker = window.dashboardQsoWorker;
	if (qsoWorker && window.WavelogWorker && WavelogWorker.isAvailable()) {
		qsoWorkerLive = true;
		WavelogWorker.subscribe({
			topic: qsoWorker.topic,
			token: qsoWorker.token,
			onMessage: function (frame) {
				if (frame.type === 'push' && frame.payload && frame.payload.type === 'qso_changed') {
					throttledQsoRefresh();
				}
			},
			onReconnect: throttledQsoRefresh,
			onFailed: function () {
				qsoWorkerLive = false;
				applyQsoPolling();
			}
		});
	}

	document.addEventListener('visibilitychange', function () {
		applyQsoPolling();
		if (!document.hidden && (qsoHiddenMissed || !qsoWorkerLive)) {
			qsoHiddenMissed = false;
			throttledQsoRefresh();
		}
	});
	applyQsoPolling();
});
