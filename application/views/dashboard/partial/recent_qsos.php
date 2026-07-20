<div class="card">
	<div class="card-header py-2">
		<h6 class="mb-0"><i class="fas fa-list"></i> <?= __("Recent QSOs"); ?></h6>
	</div>
	<div class="card-body p-0">
		<div class="table-responsive">
			<table class="table table-striped table-hover mb-0" aria-label="<?= __("Recent QSOs"); ?>">
				<thead>
					<tr>
						<th scope="col"><?= __("Date"); ?></th>
						<?php if(($this->config->item('use_auth') && ($this->session->userdata('user_type') >= 2)) || $this->config->item('use_auth') === FALSE || ($this->config->item('show_time'))) { ?>
						<th scope="col"><?= __("Time"); ?></th>
						<?php } ?>
						<th scope="col"><?= __("Callsign"); ?></th>
						<?php
						dashboard_table_header_col($this->session->userdata('user_column1')==""?'Mode':$this->session->userdata('user_column1'));
						dashboard_table_header_col($this->session->userdata('user_column2')==""?'RSTS':$this->session->userdata('user_column2'));
						dashboard_table_header_col($this->session->userdata('user_column3')==""?'RSTR':$this->session->userdata('user_column3'));
						dashboard_table_header_col($this->session->userdata('user_column4')==""?'Band':$this->session->userdata('user_column4'));
						?>
					</tr>
				</thead>
				<?php
				$i = 0;
				if ($last_qsos_list && $last_qsos_list->num_rows() > 0) {
					foreach ($last_qsos_list->result() as $row) {
						$custom_date_format = $this->session->userdata('user_date_format') ?: $this->config->item('qso_date_format');
						$timestamp = strtotime($row->COL_TIME_ON ?? '1970-01-01 00:00:00');
				?>
					<tr id="qso_<?php echo $row->COL_PRIMARY_KEY; ?>" class="tr<?php echo $i & 1; ?>">
						<td><?php echo date($custom_date_format, $timestamp); ?></td>
						<?php if(($this->config->item('use_auth') && ($this->session->userdata('user_type') >= 2)) || $this->config->item('use_auth') === FALSE || ($this->config->item('show_time'))) { ?>
						<td><?php echo date('H:i', $timestamp); ?></td>
						<?php } ?>
						<td>
							<button type="button" class="btn btn-link text-decoration-none p-0" onclick="displayQso(<?php echo $row->COL_PRIMARY_KEY; ?>)" aria-label="<?= __("View QSO"); ?> <?php echo $row->COL_CALL; ?>"><?php echo str_replace("0","&Oslash;",strtoupper($row->COL_CALL)); ?></button>
						</td>
						<?php
						dashboard_table_col($row, $this->session->userdata('user_column1')==""?'Mode':$this->session->userdata('user_column1'));
						dashboard_table_col($row, $this->session->userdata('user_column2')==""?'RSTS':$this->session->userdata('user_column2'));
						dashboard_table_col($row, $this->session->userdata('user_column3')==""?'RSTR':$this->session->userdata('user_column3'));
						dashboard_table_col($row, $this->session->userdata('user_column4')==""?'Band':$this->session->userdata('user_column4'));
						?>
					</tr>
				<?php
						$i++;
					}
				}
				?>
			</table>
		</div>
	</div>
</div>
<small class="mb-3 me-2" style="float: right;">
	<?= sprintf(_ngettext("Max. %d previous contact is shown", "Max. %d previous contacts are shown", intval($last_qso_count)), intval($last_qso_count)); ?>
</small>
