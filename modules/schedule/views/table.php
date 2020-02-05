<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    'subject',
    'CONCAT(firstname," ", lastname)',
    'achievement',
    'start_date',
    'end_date',
    'schedule_type',
];

$sIndexColumn = 'id';
$sTable       = db_prefix() . 'schedule';

$join = ['LEFT JOIN ' . db_prefix() . 'staff ON ' . db_prefix() . 'staff.staffid = ' . db_prefix() . 'schedule.staff_id'];

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, [], ['id']);

$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];
    for ($i = 0; $i < count($aColumns); $i++) {
        $_data = $aRow[$aColumns[$i]];
        if ($aColumns[$i] == 'subject') {
            $_data = '<a href="' . admin_url('schedule/edit/' . $aRow['id']) . '">' . $_data . '</a>';
            $_data .= '<div class="row-options">';
            $_data .= '<a href="' . admin_url('schedule/edit/' . $aRow['id']) . '">' . _l('view') . '</a>';

            if (has_permission('schedule', '', 'delete')) {
                $_data .= ' | <a href="' . admin_url('schedule/delete/' . $aRow['id']) . '" class="text-danger _delete">' . _l('delete') . '</a>';
            }
            $_data .= '</div>';
        } elseif ($aColumns[$i] == 'start_date' || $aColumns[$i] == 'end_date') {
            $_data = _d($_data);
        } elseif ($aColumns[$i] == 'schedule_type') {
            $_data = format_schedule_type($_data);
        }
        $row[] = $_data;
    }
    ob_start();
    $achievement          = $this->ci->schedule_model->calculate_schedule_achievement($aRow['id']);
    $percent              = $achievement['percent'];
    $progress_bar_percent = $achievement['progress_bar_percent']; ?>
    <input type="hidden" value="<?php
    echo $progress_bar_percent; ?>" name="percent">
    <div class="schedule-progress" data-reverse="true">
       <strong class="schedule-percent"><?php
        echo $percent; ?>%</strong>
    </div>
    <?php
    $progress = ob_get_contents();
    ob_end_clean();
    $row[]              = $progress;
    $row['DT_RowClass'] = 'has-row-options';
    $output['aaData'][] = $row;
}
