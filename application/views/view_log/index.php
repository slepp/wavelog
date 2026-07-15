<div class="container px-3 px-lg-4 mt-3 mb-3" id="main-content">
	<h2><?= __("Logbook"); ?></h2>
	<div class="card">
		<?php if ($results) { ?>
			<div class="card-header py-2">
				<h6 class="mb-0"><i class="fas fa-list"></i> <?= __("Active Logbook"); ?>: <span class="badge text-bg-info ms-1"><?php echo $this->logbooks_model->find_name($this->session->userdata('active_station_logbook')); ?></span><i id="directory_tooltip" data-bs-toggle="tooltip" data-bs-placement="right" class="fas fa-question-circle text-muted ms-2" data-bs-custom-class="custom-tooltip" data-bs-html="true" data-bs-title="<?= __("Displaying all QSOs of station locations which are linked to this logbook"); ?>" role="img" aria-label="<?= __("Displaying all QSOs of station locations which are linked to this logbook"); ?>"></i></h6>
			</div>
		<?php } ?>
		<?php if ($this->session->flashdata('notice')) { ?>
			<div class="alert alert-info" role="alert">
				<?php echo $this->session->flashdata('notice'); ?>
			</div>
		<?php } ?>

		<?php if ($this->optionslib->get_option('logbook_map') != "false") { ?>
			<script>
				let user_map_custom = JSON.parse('<?php echo $user_map_custom; ?>');
			</script>
			<!-- Map -->
			<div id="map" class="map-leaflet" style="width: 100%; height: 350px"></div>
		<?php } ?>
	</div>

	<?php $live_capable = $live_capable ?? false; // live logbook only refreshes page 1; set by Logbook::index() ?>
	<div class="card">
		<div class="card-header py-2 d-flex align-items-center justify-content-between">
			<h6 class="mb-0"><i class="fas fa-list"></i> <?= __("Recent QSOs"); ?></h6>
			<?php if ($live_capable) { ?>
				<button type="button" id="live_logbook_toggle" class="btn btn-sm btn-outline-secondary py-0" data-bs-toggle="tooltip" data-bs-title="<?= __("Automatically refresh the logbook when QSOs change"); ?>">
					<i class="fas fa-circle me-1"></i><?= __("Live"); ?>
				</button>
			<?php } ?>
		</div>
		<div class="card-body">
			<div id="logbook-table-container"
				<?php if ($live_capable) { ?>
				data-live-url="<?php echo site_url('logbook/live_table'); ?>"
				data-live-enabled="<?php echo (($live_mode_enabled ?? 'true') == 'true') ? '1' : '0'; ?>"
				data-save-pref-url="<?php echo site_url('user_options/save_logbook_pref'); ?>"
				<?php if (!empty($logbook_live_worker)) { ?>
				data-worker-topic="<?php echo html_escape($logbook_live_worker['topic']); ?>"
				data-worker-token="<?php echo html_escape($logbook_live_worker['token']); ?>"
				<?php } ?>
				<?php } ?>
				>
				<?php $this->load->view('view_log/partial/log_ajax') ?>
			</div>
		</div>
	</div>
</div>
