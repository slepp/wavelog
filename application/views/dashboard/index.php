
<script>
	let user_map_custom = JSON.parse('<?php echo $user_map_custom; ?>');
	window.radiosUserWorker = <?php echo json_encode($radios_user_worker ?? null); ?>;
	window.dashboardQsoWorker = <?php echo json_encode($dashboard_qso_worker ?? null); ?>;
</script>

<div class="container dashboard px-3 px-lg-4 mt-3 mb-3" id="main-content">
<?php if(($this->config->item('use_auth') && ($this->session->userdata('user_type') >= 2)) || $this->config->item('use_auth') === FALSE) { ?>

	<?php if (version_compare(PHP_VERSION, '8.0.0') <= 0) { ?>
		<div class="alert alert-danger alert-dismissible fade show" role="alert">
			<?= sprintf(__("You need to upgrade your PHP version. Minimum version is %s. Your version is: %s."), "8.0", PHP_VERSION); ?>
			<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
		</div>
	<?php } ?>
	<?php if(($this->session->userdata('user_type') == 99) && !($this->config->item('disable_version_check') ?? false) && $this->optionslib->get_option('latest_release')) { ?>
		<?php if (version_compare($this->optionslib->get_option('latest_release'), $this->optionslib->get_option('version'), '>')) { ?>
			<div class="alert alert-success alert-dismissible fade show" role="alert" style="margin-top: 1rem;">
				<?= sprintf(_pgettext("Dashboard Warning", "A new version of Wavelog has been published. See: %s."), "<a href=\"https://github.com/wavelog/wavelog/releases/tag/".$this->optionslib->get_option('latest_release')."\" target=\"_blank\"><u>Release ".$this->optionslib->get_option('latest_release')."</u></a>"); ?>
				<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
			</div>
		<?php } ?>
	<?php } ?>

	<?php if ($countryCount == 0) { ?>
		<div class="alert alert-danger mt-3 alert-dismissible fade show" role="alert">
		<?= sprintf(
				_pgettext("Dashboard Warning", "You need to update country files! Click %shere%s to do it."), '<u><a href="' . site_url('update') . '">', "</a></u>"
			); ?>
			<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
		</div>
	<?php } ?>

	<?php if ($locationCount == 0 && !$is_first_login) { ?>
		<div class="alert alert-danger alert-dismissible fade show" role="alert">
		<?= sprintf(
				_pgettext("Dashboard Warning", "You have no station locations. Click %shere%s to do it."), '<u><a href="' . site_url('stationsetup') . '">', '</a></u>'
			); ?>
			<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
		</div>
	<?php } ?>

	<?php if ($logbookCount == 0 && !$is_first_login) { ?>
		<div class="alert alert-danger alert-dismissible fade show" role="alert">
		<?= sprintf(
				_pgettext("Dashboard Warning", "You have no station logbook. Click %shere%s to do it."), '<u><a href="' . site_url('stationsetup') . '">', '</a></u>'
			); ?>
			<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
		</div>
	<?php } ?>

	<?php if (($linkedCount > 0) && $active_not_linked && !$is_first_login) { ?>
		<div class="alert alert-danger alert-dismissible fade show" role="alert">
		<?= sprintf(
				_pgettext("Dashboard Warning", "Your active station location is not linked to your active station logbook. Click %shere%s to do it."), '<u><a href="' . site_url('stationsetup') . '">', '</a></u>'
			); ?>
			<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
		</div>
	<?php } ?>

	<?php if ($linkedCount == 0 && !$is_first_login) { ?>
		<div class="alert alert-danger alert-dismissible fade show" role="alert">
		<?= sprintf(
				_pgettext("Dashboard Warning", "You have no station linked to your logbook. Click %shere%s to do it."), '<u><a href="' . site_url('stationsetup') . '">', '</a></u>'
			); ?>
			<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
		</div>
	<?php } ?>

	<?php if($dashboard_banner != "false" && $this->session->userdata('clubstation') == 0) { ?>
	<?php if($todays_qsos >= 1) { ?>
		<div class="alert alert-success alert-dismissible fade show" role="alert" style="margin-top: 1rem;">
			<?= sprintf(
					_ngettext("You have had %d QSO today", "You have had %d QSOs today", intval($todays_qsos)),
					intval($todays_qsos)
				); ?>
			<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
		</div>
	<?php } else { ?>
		<div class="alert alert-warning alert-dismissible fade show" role="alert" style="margin-top: 1rem;">
			<span class="badge text-bg-info"><?= __("Important"); ?></span> <i class="fas fa-broadcast-tower"></i>
			<?php if (($almost_current_streak ?? 0)>0) {
				echo sprintf(__("Don't lose your streak - You have already had at least one QSO for the last %s consecutive days."),$almost_current_streak);
			} else {
				echo __("You have made no QSOs today; time to turn on the radio!");
			} ?>
			<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
		</div>
	<?php } ?>
	<?php } ?>

	<?php if($current_active == 0 && !$is_first_login) { ?>
		<div class="alert alert-danger alert-dismissible fade show" role="alert">
			<?= __("Attention: you need to set an active station location."); ?>
			<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
		</div>
	<?php } ?>

	<?php if($themesWithoutMode != 0) { ?>
		<div class="alert alert-danger alert-dismissible fade show" role="alert">
			<?= __("You have themes without defined theme mode. Please ask the admin to edit the themes."); ?>
			<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
		</div>
	<?php } ?>

	<?php if ($this->session->userdata('user_id')) { ?>
		<?php if ($lotw_cert_expired == true || $lotw_cert_expiring == true) { ?>
			<?php
				if($lotw_cert_expired == true) { ?>
				<div class="alert alert-danger alert-dismissible fade show" role="alert">
				<?php } elseif($lotw_cert_expiring == true) { ?>
				<div class="alert alert-warning alert-dismissible fade show" role="alert">
				<?php } ?>
					<span class="badge text-bg-info"><?= __("Important"); ?></span> <i class="fas fa-hourglass-half"></i> <?= sprintf(_pgettext("LoTW Warning", "LoTW Warning: There is an issue with at least one of your %sLoTW certificates%s!"), '<u><a href="'.site_url('lotw').'">', "</a></u>"); ?>
					<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
					<ul style="margin-top: 0.5em; margin-bottom: 0.1em;">
						<?php if ($lotw_cert_expired == true) { echo "<li>".__("At least one of your certificates is expired"); } ?>
						<?php if ($lotw_cert_expiring == true) { echo "<li>".__("At least one of your certificates is expiring"); } ?>
					</ul>
				</div>
		<?php } ?>
	<?php } ?>

<?php } ?>
<?php $this->load->view('layout/messages'); ?>
</div>

<?php if (!empty($dashboard_show_kpi_stats)) : ?>
<!-- KPI Stat Cards -->
<div class="container dashboard px-3 px-lg-4 mt-3 mb-3" id="kpi-cards" title="<?= __("Right-click for options"); ?>">
	<div class="row g-3">
		<div class="col-6 col-md-4 col-lg-2">
			<div class="card h-100">
				<div class="card-header py-2">
					<h6 class="mb-0"><i class="fas fa-list-ol"></i> <?= __("Total QSOs"); ?></h6>
				</div>
				<div class="card-body p-0">
					<h4 id="dashboard-total-qsos" class="fw-bold mb-0 px-3 py-2"><?php echo $total_qsos; ?></h4>
				</div>
			</div>
		</div>
		<div class="col-6 col-md-4 col-lg-2">
			<div class="card h-100">
				<div class="card-header py-2">
					<h6 class="mb-0"><i class="fas fa-calendar"></i> <?= __("QSOs this year"); ?></h6>
				</div>
				<div class="card-body p-0">
					<h4 id="dashboard-year-qsos" class="fw-bold mb-0 px-3 py-2"><?php echo $year_qsos; ?></h4>
				</div>
			</div>
		</div>
		<div class="col-6 col-md-4 col-lg-2">
			<div class="card h-100">
				<div class="card-header py-2">
					<h6 class="mb-0"><i class="fas fa-calendar-day"></i> <?= __("QSOs this month"); ?></h6>
				</div>
				<div class="card-body p-0">
					<h4 id="dashboard-month-qsos" class="fw-bold mb-0 px-3 py-2"><?php echo $month_qsos; ?></h4>
				</div>
			</div>
		</div>
		<div class="col-6 col-md-4 col-lg-2">
			<div class="card h-100">
				<div class="card-header py-2">
					<h6 class="mb-0"><i class="fas fa-clock"></i> <?= __("QSOs today"); ?></h6>
				</div>
				<div class="card-body p-0">
					<h4 id="dashboard-todays-qsos" class="fw-bold mb-0 px-3 py-2"><?php echo $todays_qsos; ?></h4>
				</div>
			</div>
		</div>
		<div class="col-6 col-md-4 col-lg-2">
			<div class="card h-100">
				<div class="card-header py-2">
					<h6 class="mb-0"><i class="fas fa-fire"></i> <?= __("Current Streak"); ?> <a href="<?php echo site_url('dayswithqso'); ?>#streaks" aria-label="<?= __("Current Streak"); ?>"><i class="fa-solid fa-up-right-from-square" aria-hidden="true"></i></a></h6>
				</div>
				<div class="card-body p-0">
					<h4 id="dashboard-current-streak" class="fw-bold mb-0 px-3 py-2"><?= sprintf(_ngettext("%d Day", "%d Days", (int) $current_streak), (int) $current_streak) ?></h4>
				</div>
			</div>
		</div>
		<div class="col-6 col-md-4 col-lg-2">
			<div class="card h-100">
				<div class="card-header py-2">
					<h6 class="mb-0"><i class="fas fa-users"></i> <?= __("Unique callsigns"); ?> <a href="<?php echo site_url('statistics'); ?>#uniquetab" aria-label="<?= __("Unique callsigns"); ?>"><i class="fa-solid fa-up-right-from-square" aria-hidden="true"></i></a></h6>
				</div>
				<div class="card-body p-0">
					<h4 id="dashboard-unique-callsigns" class="fw-bold mb-0 px-3 py-2"><?php echo $unique_callsigns; ?></h4>
				</div>
			</div>
		</div>
	</div>
	</div>
<?php $this->load->view('dashboard/_options_menu', ['menu_id'=>'kpiOptsMenu','target_id'=>'kpi-cards']); ?>
<?php endif; ?>

<div class="container dashboard px-3 px-lg-4 mt-3 mb-3">

<!-- Log Data -->
<div class="row g-3 logdata<?php if($dashboard_map == "Y") { ?> has-map<?php } ?>">

	<!-- Map -->
		<?php if($dashboard_map == "Y") { ?>
		<div class="col-12 map-breakout">
			<div class="card">
				<div class="card-header py-2">
					<h6 class="mb-0"><i class="fas fa-map-marked-alt"></i> <?= __("Map"); ?></h6>
				</div>
				<div class="card-body p-0">
					<div id="map" class="map-leaflet" style="width: 100%; height: 350px"></div>
				</div>
			</div>
		</div>
		<?php } ?>

		<!-- Left column: QSOs -->
	<div class="col-12 col-lg-8">

		<?php if($dashboard_map == "map_at_left") { ?>
		<div class="card mb-3">
			<div class="card-header py-2">
				<h6 class="mb-0"><i class="fas fa-map-marked-alt"></i> <?= __("Map"); ?></h6>
			</div>
			<div class="card-body p-0">
				<div id="map" class="map-leaflet" style="width: 100%; height: 350px;"></div>
			</div>
		</div>
		<?php } ?>
		<div id="dashboard-recent-qsos">
			<?php $this->load->view('dashboard/partial/recent_qsos', ['last_qsos_list' => $last_qsos_list, 'last_qso_count' => $last_qso_count]); ?>
		</div>
	</div>

	<!-- Right column: cards -->
	<div class="col-12 col-lg-4">
		<?php if($dashboard_map == "map_at_right") { ?>
		<div class="card mb-3">
			<div class="card-header py-2">
				<h6 class="mb-0"><i class="fas fa-map-marked-alt"></i> <?= __("Map"); ?></h6>
			</div>
			<div class="card-body p-0">
				<div id="map" class="map-leaflet" style="width: 100%; height: 350px;"></div>
			</div>
		</div>
		<?php } ?>

		<!-- radio status component -->
		<div id="radio_display"></div>


			<?php if (($dashboard_solar ?? 'N') === 'top') { $this->load->view('dashboard/solar_data'); } ?>
			<?php if (!empty($active_dxpeditions) && $active_dxpeditions !== false) { ?>
			<div class="card mb-3">
				<div class="card-header py-2">
					<h6 class="mb-0"><i class="fas fa-broadcast-tower"></i> <?= __("Active Expeditions"); ?> <a href="<?php echo site_url('dxcalendar'); ?>" aria-label="<?= __("Active Expeditions"); ?>"><i class="fa-solid fa-up-right-from-square" aria-hidden="true"></i></a></h6>
				</div>
				<div class="card-body p-0">
				<table class="table table-striped table-hover mb-0" aria-label="<?= __("Active Expeditions"); ?>">
					<thead>
						<tr>
							<th scope="col"><?= __("Call"); ?></th>
							<th scope="col"><?= __("DXCC"); ?></th>
							<th scope="col"><?= __("Dates"); ?></th>
							<th scope="col"><?= __("QSL"); ?></th>
						</tr>
					</thead>
						<tbody>
							<?php foreach ($active_dxpeditions as $dxped) { ?>
							<tr>
								<td>
									<?php if ($dxped->link) { ?>
										<a href="<?= $dxped->link; ?>" target="_blank">
									<?php } ?>
								<span class="badge <?= $dxped->call_cnfmd ? 'bg-success' : ($dxped->call_wked ? 'bg-warning' : 'bg-danger'); ?>">
									<?= $dxped->call; ?><span class="visually-hidden"> (<?= $dxped->call_cnfmd ? __("Confirmed") : ($dxped->call_wked ? __("Worked") : __("Not worked")); ?>)</span>
								</span>
									<?php if ($dxped->link) { echo '</a>'; } ?>
								</td>
								<td>
									<?php if ($dxped->dxcc_adif >= 1) { ?>
										<button type="button" class="btn btn-link text-decoration-none p-0 align-baseline" onclick="spawnLookupModal('<?= $dxped->dxcc_adif; ?>','dxcc')" aria-label="<?= __("Lookup DXCC"); ?> <?= $dxped->dxcc; ?>">
									<?php } ?>
								<span class="badge <?= $dxped->dxcc_cnfmd ? 'bg-success' : ($dxped->dxcc_wked ? 'bg-warning' : ($dxped->no_dxcc ? '' : 'bg-danger')); ?>">
									<?= $dxped->dxcc; ?><span class="visually-hidden"> (<?= $dxped->dxcc_cnfmd ? __("Confirmed") : ($dxped->dxcc_wked ? __("Worked") : __("Not worked")); ?>)</span>
								</span>
									<?php if ($dxped->dxcc_adif >= 1) { echo '</button>'; } ?>
								</td>
								<td><small><?= ($dxped->dates[0] ?? '') . ' - ' . ($dxped->dates[1] ?? ''); ?></small></td>
								<td><small><?= $dxped->qslinfo; ?></small></td>
							</tr>
							<?php } ?>
						</tbody>
					</table>
				</div>
			</div>
			<?php } ?>

			<?php if (!empty($active_contests) && $active_contests !== false) { ?>
			<div class="card mb-3">
				<div class="card-header py-2">
					<h6 class="mb-0"><i class="fas fa-trophy"></i> <?= __("Active Contests"); ?> <a href="<?php echo site_url('contestcalendar'); ?>" aria-label="<?= __("Active Contests"); ?>"><i class="fa-solid fa-up-right-from-square" aria-hidden="true"></i></a></h6>
				</div>
				<div class="card-body p-0">
					<table class="table table-striped table-hover mb-0">
						<tbody>
							<?php foreach ($active_contests as $contest) { ?>
							<tr>
								<td>
									<b><?= $contest['title']; ?></b>
									<?php
									if ($contest['start'] instanceof DateTime && $contest['end'] instanceof DateTime) {
										echo '<br><small class="text-muted">' . $contest['start']->format('d M H:i') . ' - ' . $contest['end']->format('d M H:i') . '</small>';
									}
									?>
								</td>
								<td class="text-end align-middle">
									<a class="btn btn-sm btn-outline-primary" href="<?= $contest['link']; ?>" target="_blank"><?= __("Details"); ?></a>
								</td>
							</tr>
							<?php } ?>
						</tbody>
					</table>
				</div>
			</div>
			<?php } ?>
		<div class="card mb-3">
			<div class="card-header py-2">
				<h6 class="mb-0"><i class="fas fa-globe-europe"></i> <?= __("DXCCs Breakdown"); ?> <a href="<?php echo site_url('awards/dxcc'); ?>" aria-label="<?= __("DXCCs Breakdown"); ?>"><i class="fa-solid fa-up-right-from-square" aria-hidden="true"></i></a></h6>
			</div>
			<div class="card-body p-0">
				<table class="table table-striped mb-0" aria-label="<?= __("DXCCs Breakdown"); ?>">
					<tr>
						<th scope="row" width="50%"><?= __("Worked"); ?></th>
						<td id="dashboard-countries-worked" width="50%"><?php echo $total_countries; ?></td>
					</tr>
					<tr>
						<th scope="row" width="50%"><?= __("Confirmed"); ?></th>
						<td width="50%">
							<span id="dashboard-countries-confirmed-qsl" title="<?= __("QSL Cards"); ?>" aria-label="<?= __("QSL Cards"); ?>: <?php echo $total_countries_confirmed_paper; ?>" data-bs-toggle="tooltip"><?php echo $total_countries_confirmed_paper; ?></span> /
							<span id="dashboard-countries-confirmed-lotw" title="<?= __("LoTW"); ?>" aria-label="<?= __("LoTW"); ?>: <?php echo $total_countries_confirmed_lotw; ?>" data-bs-toggle="tooltip"><?php echo $total_countries_confirmed_lotw; ?></span> /
							<span id="dashboard-countries-confirmed-eqsl" title="<?= __("eQSL"); ?>" aria-label="<?= __("eQSL"); ?>: <?php echo $total_countries_confirmed_eqsl; ?>" data-bs-toggle="tooltip"><?php echo $total_countries_confirmed_eqsl; ?></span>
						</td>
					</tr>
					<tr>
						<th scope="row" width="50%"><?= __("Needed"); ?></th>
						<td id="dashboard-countries-needed" width="50%"><?php echo $total_countries_needed; ?></td>
					</tr>
				</table>
			</div>
		</div>

		<?php if((($this->config->item('use_auth') && ($this->session->userdata('user_type') >= 2)) || $this->config->item('use_auth') === FALSE) && ($total_qsl_sent != 0 || $total_qsl_rcvd != 0 || $total_qsl_requested != 0)) { ?>
		<div class="card mb-3">
			<div class="card-header py-2">
				<h6 class="mb-0"><i class="fas fa-envelope"></i> <?= __("QSL Cards"); ?></h6>
			</div>
			<div class="card-body p-0">
				<table class="table table-striped mb-0" aria-label="<?= __("QSL Cards"); ?>">
					<thead>
						<tr>
							<th scope="col" width="50%"><span class="visually-hidden"><?= __("Status"); ?></span></th>
							<th scope="col" width="25%"><?= __("Total"); ?></th>
							<th scope="col" width="25%"><?= __("Today"); ?></th>
						</tr>
					</thead>
					<tr>
						<th scope="row" width="50%"><?= __("Sent"); ?></th>
						<td width="25%"><?php echo $total_qsl_sent; ?></td>
						<td width="25%"><?php echo $qsl_sent_today != 0 ? "<button type=\"button\" class=\"btn btn-link text-decoration-none p-0\" onclick=\"displayContacts('','All','All','All','All','QSLSDATE','');\">".$qsl_sent_today."</button>" : "0"; ?></td>
					</tr>
					<tr>
						<th scope="row" width="50%"><a href="<?php echo site_url('generic_qsl/confirmations/qsl'); ?>"><?= __("Received"); ?></a></th>
						<td width="25%"><?php echo $total_qsl_rcvd; ?></td>
						<td width="25%"><?php echo $qsl_rcvd_today != 0 ? "<button type=\"button\" class=\"btn btn-link text-decoration-none p-0\" onclick=\"displayContacts('','All','All','All','All','QSLRDATE','');\">".$qsl_rcvd_today."</button>" : "0"; ?></td>
					</tr>
					<tr>
						<th scope="row" width="50%"><?= __("Requested"); ?></th>
						<td width="25%"><?php echo $total_qsl_requested; ?></td>
						<td width="25%"><?php echo $qsl_requested_today; ?></td>
					</tr>
				</table>
			</div>
		</div>
		<?php } ?>

		<?php if((($this->config->item('use_auth') && ($this->session->userdata('user_type') >= 2)) || $this->config->item('use_auth') === false) && ($total_lotw_sent != 0 || $total_lotw_rcvd != 0)) { ?>
		<div class="card mb-3">
			<div class="card-header py-2">
				<h6 class="mb-0"><i class="fas fa-list"></i> <?= _pgettext("Probably no translation needed as this is a name.","Logbook of the World"); ?></h6>
			</div>
			<div class="card-body p-0">
				<table class="table table-striped mb-0" aria-label="<?= _pgettext("Probably no translation needed as this is a name.","Logbook of the World"); ?>">
					<thead>
						<tr>
							<th scope="col" width="50%"><span class="visually-hidden"><?= __("Status"); ?></span></th>
							<th scope="col" width="25%"><?= __("Total"); ?></th>
							<th scope="col" width="25%"><?= __("Today"); ?></th>
						</tr>
					</thead>
					<tr>
						<th scope="row" width="50%"><?= __("Sent"); ?></th>
						<td width="25%"><?php echo $total_lotw_sent; ?></td>
						<td width="25%"><?php echo $lotw_sent_today != 0 ? "<button type=\"button\" class=\"btn btn-link text-decoration-none p-0\" onclick=\"displayContacts('','all','all','All','All','LOTWSDATE','');\">".$lotw_sent_today."</button>" : "0"; ?></td>
					</tr>
					<tr>
						<th scope="row" width="50%"><a href="<?php echo site_url('generic_qsl/confirmations/lotw'); ?>"><?= __("Received"); ?></a></th>
						<td width="25%"><?php echo $total_lotw_rcvd; ?></td>
						<td width="25%"><?php echo $lotw_rcvd_today != 0 ? "<button type=\"button\" class=\"btn btn-link text-decoration-none p-0\" onclick=\"displayContacts('','all','all','All','All','LOTWRDATE','');\">".$lotw_rcvd_today."</button>" : "0"; ?></td>
					</tr>
				</table>
			</div>
		</div>
		<?php } ?>

		<?php if((($this->config->item('use_auth') && ($this->session->userdata('user_type') >= 2)) || $this->config->item('use_auth') === FALSE) && ($total_eqsl_sent != 0 || $total_eqsl_rcvd != 0)) { ?>
		<div class="card mb-3">
			<div class="card-header py-2">
				<h6 class="mb-0"><i class="fas fa-address-card"></i> <?= __("eQSL Cards"); ?></h6>
			</div>
			<div class="card-body p-0">
				<table class="table table-striped mb-0" aria-label="<?= __("eQSL Cards"); ?>">
					<thead>
						<tr>
							<th scope="col" width="50%"><span class="visually-hidden"><?= __("Status"); ?></span></th>
							<th scope="col" width="25%"><?= __("Total"); ?></th>
							<th scope="col" width="25%"><?= __("Today"); ?></th>
						</tr>
					</thead>
					<tr>
						<th scope="row" width="50%"><?= __("Sent"); ?></th>
						<td width="25%"><?php echo $total_eqsl_sent; ?></td>
						<td width="25%"><?php echo $eqsl_sent_today != 0 ? "<button type=\"button\" class=\"btn btn-link text-decoration-none p-0\" onclick=\"displayContacts('','All','All','All','All','EQSLSDATE','');\">".$eqsl_sent_today."</button>" : "0"; ?></td>
					</tr>
					<tr>
						<th scope="row" width="50%"><a href="<?php echo site_url('generic_qsl/confirmations/eqsl'); ?>"><?= __("Received"); ?></a></th>
						<td width="25%"><?php echo $total_eqsl_rcvd; ?></td>
						<td width="25%"><?php echo $eqsl_rcvd_today != 0 ? "<button type=\"button\" class=\"btn btn-link text-decoration-none p-0\" onclick=\"displayContacts('','All','All','All','All','EQSLRDATE','');\">".$eqsl_rcvd_today."</button>" : "0"; ?></td>
					</tr>
				</table>
			</div>
		</div>
		<?php } ?>

		<?php if((($this->config->item('use_auth') && ($this->session->userdata('user_type') >= 2)) || $this->config->item('use_auth') === false) && ($total_qrz_sent != 0 || $total_qrz_rcvd != 0)) { ?>
		<div class="card mb-3">
			<div class="card-header py-2">
				<h6 class="mb-0"><i class="fas fa-list"></i> QRZ.com</h6>
			</div>
			<div class="card-body p-0">
				<table class="table table-striped mb-0" aria-label="QRZ.com">
					<thead>
						<tr>
							<th scope="col" width="50%"><span class="visually-hidden"><?= __("Status"); ?></span></th>
							<th scope="col" width="25%"><?= __("Total"); ?></th>
							<th scope="col" width="25%"><?= __("Today"); ?></th>
						</tr>
					</thead>
					<tr>
						<th scope="row" width="50%"><?= __("Sent"); ?></th>
						<td width="25%"><?php echo $total_qrz_sent; ?></td>
						<td width="25%"><?php echo $qrz_sent_today != 0 ? "<button type=\"button\" class=\"btn btn-link text-decoration-none p-0\" onclick=\"displayContacts('','all','all','All','All','QRZSDATE','');\">".$qrz_sent_today."</button>" : "0"; ?></td>
					</tr>
					<tr>
						<th scope="row" width="50%"><a href="<?php echo site_url('generic_qsl/confirmations/qrz'); ?>"><?= __("Received"); ?></a></th>
						<td width="25%"><?php echo $total_qrz_rcvd; ?></td>
						<td width="25%"><?php echo $qrz_rcvd_today != 0 ? "<button type=\"button\" class=\"btn btn-link text-decoration-none p-0\" onclick=\"displayContacts('','all','all','All','All','QRZRDATE','');\">".$qrz_rcvd_today."</button>" : "0"; ?></td>
					</tr>
				</table>
			</div>
		</div>
		<?php } ?>

		<?php if((($this->config->item('use_auth') && ($this->session->userdata('user_type') >= 2)) || $this->config->item('use_auth') === false) && ($total_clublog_sent != 0 || $total_clublog_rcvd != 0)) { ?>
		<div class="card mb-3">
			<div class="card-header py-2">
				<h6 class="mb-0"><i class="fas fa-list"></i> Club Log</h6>
			</div>
			<div class="card-body p-0">
				<table class="table table-striped mb-0" aria-label="<?= __("Club Log"); ?>">
					<thead>
						<tr>
							<th scope="col" width="50%"><span class="visually-hidden"><?= __("Status"); ?></span></th>
							<th scope="col" width="25%"><?= __("Total"); ?></th>
							<th scope="col" width="25%"><?= __("Today"); ?></th>
						</tr>
					</thead>
					<tr>
						<th scope="row" width="50%"><?= __("Sent"); ?></th>
						<td width="25%"><?php echo $total_clublog_sent; ?></td>
						<td width="25%"><?php echo $clublog_sent_today != 0 ? "<button type=\"button\" class=\"btn btn-link text-decoration-none p-0\" onclick=\"displayContacts('','all','all','All','All','CLUBLOGSDATE','');\">".$clublog_sent_today."</button>" : "0"; ?></td>
					</tr>
					<tr>
						<th scope="row" width="50%"><a href="<?php echo site_url('generic_qsl/confirmations/clublog'); ?>"><?= __("Received"); ?></a></th>
						<td width="25%"><?php echo $total_clublog_rcvd; ?></td>
						<td width="25%"><?php echo $clublog_rcvd_today != 0 ? "<button type=\"button\" class=\"btn btn-link text-decoration-none p-0\" onclick=\"displayContacts('','all','all','All','All','CLUBLOGRDATE','');\">".$clublog_rcvd_today."</button>" : "0"; ?></td>
					</tr>
				</table>
			</div>
		</div>
		<?php } ?>

		<?php if((($this->config->item('use_auth') && ($this->session->userdata('user_type') >= 2)) || $this->config->item('use_auth') === FALSE)) { ?>
		<div class="card mb-3">
			<div class="card-header py-2">
				<h6 class="mb-0"><i class="fas fa-globe-europe"></i> <?= __("VUCC-Grids"); ?> <a href="<?php echo site_url('awards/vucc'); ?>" aria-label="<?= __("VUCC-Grids"); ?>"><i class="fa-solid fa-up-right-from-square" aria-hidden="true"></i></a></h6>
			</div>
			<div class="card-body p-0">
				<table class="table table-striped mb-0" aria-label="<?= __("VUCC-Grids"); ?>">
					<thead>
						<tr>
							<th scope="col" width="50%"><span class="visually-hidden"><?= __("Status"); ?></span></th>
							<th scope="col" width="25%"><?= __("All"); ?></th>
							<th scope="col" width="25%"><?= __("SAT"); ?></th>
						</tr>
					</thead>
					<tr>
						<th scope="row" width="50%"><?= __("Worked"); ?></th>
						<td width="25%"><?php echo $vucc['All']['worked']; ?></td>
						<td width="25%"><?php echo $vuccSAT['SAT']['worked'] ?? '0'; ?></td>
					</tr>
					<tr>
						<th scope="row" width="50%"><?= __("Confirmed"); ?></th>
						<td width="25%"><?php echo $vucc['All']['confirmed']; ?></td>
						<td width="25%"><?php echo $vuccSAT['SAT']['confirmed'] ?? '0'; ?></td>
					</tr>
				</table>
			</div>
		</div>
		<?php } ?>

		<?php if (in_array(($dashboard_solar ?? 'N'), ['bottom', 'Y'], true)) { $this->load->view('dashboard/solar_data'); } ?>

	</div>

</div>
<?php echo $firstloginwizard; ?>
</div>
