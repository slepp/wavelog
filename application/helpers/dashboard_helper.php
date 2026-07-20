<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('dashboard_table_header_col')) {
	function dashboard_table_header_col($name) {
		switch($name) {
			case 'Mode': echo '<th scope="col">'.__("Mode").'</th>'; break;
			case 'RSTS': echo '<th scope="col" class="d-none d-sm-table-cell">'.__("RSTS").'</th>'; break;
			case 'RSTR': echo '<th scope="col" class="d-none d-sm-table-cell">'.__("RSTR").'</th>'; break;
			case 'Country': echo '<th scope="col">'.__("Country").'</th>'; break;
			case 'IOTA': echo '<th scope="col">'.__("IOTA").'</th>'; break;
			case 'SOTA': echo '<th scope="col">'.__("SOTA").'</th>'; break;
			case 'WWFF': echo '<th scope="col">'.__("WWFF").'</th>'; break;
			case 'POTA': echo '<th scope="col">'.__("POTA").'</th>'; break;
			case 'State': echo '<th scope="col">'.__("State").'</th>'; break;
			case 'Grid': echo '<th scope="col">'.__("Gridsquare").'</th>'; break;
			case 'Distance': echo '<th scope="col">'.__("Distance").'</th>'; break;
			case 'Band': echo '<th scope="col">'.__("Band").'</th>'; break;
			case 'Frequency': echo '<th scope="col">'.__("Frequency").'</th>'; break;
			case 'Operator': echo '<th scope="col">'.__("Operator").'</th>'; break;
			case 'Name': echo '<th scope="col">'.__("Name").'</th>'; break;
			case 'Bearing': echo '<th scope="col">'.__("Bearing").'</th>'; break;
		}
	}
}

if (!function_exists('dashboard_table_col')) {
	function dashboard_table_col($row, $name) {
		$ci =& get_instance();
		switch($name) {
			case 'Mode': echo '<td>'; echo $row->COL_SUBMODE==null?$row->COL_MODE:$row->COL_SUBMODE . '</td>'; break;
			case 'RSTS': echo '<td class="d-none d-sm-table-cell">' . $row->COL_RST_SENT; if ($row->COL_STX) { echo ' <span data-bs-toggle="tooltip" title="'.($row->COL_CONTEST_ID!=""?$row->COL_CONTEST_ID:"n/a").'" class="badge text-bg-light">'; printf("%03d", $row->COL_STX); echo '</span>';} if ($row->COL_STX_STRING) { echo ' <span data-bs-toggle="tooltip" title="'.($row->COL_CONTEST_ID!=""?$row->COL_CONTEST_ID:"n/a").'" class="badge text-bg-light">' . $row->COL_STX_STRING . '</span>';} echo '</td>'; break;
			case 'RSTR': echo '<td class="d-none d-sm-table-cell">' . $row->COL_RST_RCVD; if ($row->COL_SRX) { echo ' <span data-bs-toggle="tooltip" title="'.($row->COL_CONTEST_ID!=""?$row->COL_CONTEST_ID:"n/a").'" class="badge text-bg-light">'; printf("%03d", $row->COL_SRX); echo '</span>';} if ($row->COL_SRX_STRING) { echo ' <span data-bs-toggle="tooltip" title="'.($row->COL_CONTEST_ID!=""?$row->COL_CONTEST_ID:"n/a").'" class="badge text-bg-light">' . $row->COL_SRX_STRING . '</span>';} echo '</td>'; break;
			case 'Country': echo '<td>' . ucwords(strtolower(($row->COL_COUNTRY))); if ($row->end != NULL) echo ' <span class="badge text-bg-danger">'.__("Deleted DXCC").'</span>'  . '</td>'; break;
			case 'IOTA': echo '<td>' . ($row->COL_IOTA) . '</td>'; break;
			case 'SOTA': echo '<td>' . ($row->COL_SOTA_REF) . '</td>'; break;
			case 'WWFF': echo '<td>' . ($row->COL_WWFF_REF) . '</td>'; break;
			case 'POTA': echo '<td>' . ($row->COL_POTA_REF) . '</td>'; break;
			case 'Grid':
				if(!$ci->load->is_loaded('Qra')) {
					$ci->load->library('qra');
				}
				echo '<td>' . ($ci->qra->echoQrbCalcLink($row->station_gridsquare, $row->COL_VUCC_GRIDS, $row->COL_GRIDSQUARE)) . '</td>'; break;
			case 'Distance': echo '<td><span data-bs-toggle="tooltip" title="'.$row->COL_GRIDSQUARE.'">' . dashboard_distance($row->COL_DISTANCE) . '</span></td>'; break;
			case 'Bearing': echo '<td><span data-bs-toggle="tooltip" title="'.($row->COL_VUCC_GRIDS!="" ? $row->COL_VUCC_GRIDS : $row->COL_GRIDSQUARE).'">' . dashboard_bearing(($row->COL_VUCC_GRIDS!="" ? $row->COL_VUCC_GRIDS : $row->COL_GRIDSQUARE)) . '</span></td>'; break;
			case 'Band': echo '<td>'; if($row->COL_SAT_NAME != null) { echo '<a href="https://db.satnogs.org/search/?q='.$row->COL_SAT_NAME.'" target="_blank">'.$row->COL_SAT_NAME.'</a></td>'; } else { echo strtolower($row->COL_BAND ?? ''); } echo '</td>'; break;
			case 'Frequency': echo '<td>'; if($row->COL_SAT_NAME != null) { echo '<a href="https://db.satnogs.org/search/?q='.$row->COL_SAT_NAME.'" target="_blank">'.$row->COL_SAT_NAME.'</a></td>'; } else { if($row->COL_FREQ != null && $row->COL_FREQ != 0) { echo $ci->frequency->qrg_conversion($row->COL_FREQ); } else { echo strtolower($row->COL_BAND ?? ''); } } echo '</td>'; break;
			case 'State': echo '<td>' . ($row->COL_STATE) . '</td>'; break;
			case 'Operator': echo '<td>' . ($row->COL_OPERATOR) . '</td>'; break;
			case 'Name': echo '<td>' . ($row->COL_NAME) . '</td>'; break;
		}
	}
}

if (!function_exists('dashboard_bearing')) {
	function dashboard_bearing($grid = '') {
		if ($grid == '') return '';
		$ci =& get_instance();
		if (($ci->session->userdata('user_locator') ?? '') != '') {
			if(!$ci->load->is_loaded('qra')) {
				$ci->load->library('qra');
			}
			$bearing = $ci->qra->get_bearing($ci->session->userdata('user_locator'), $grid);
			return $bearing.'&deg;';
		}
		return '';
	}
}

if (!function_exists('dashboard_distance')) {
	function dashboard_distance($distance) {
		if (($distance ?? 0) == 0) return '';

		$ci =& get_instance();
		$measurement_base = $ci->session->userdata('user_measurement_base') ?? $ci->config->item('measurement_base');
		$unit = match ($measurement_base) {
			'M' => 'mi',
			'N' => 'nmi',
			default => 'km',
		};

		if ($unit == 'mi') {
			$distance = round($distance * 0.621371, 1);
		}
		if ($unit == 'nmi') {
			$distance = round($distance * 0.539957, 1);
		}

		return $distance . ' ' . $unit;
	}
}
