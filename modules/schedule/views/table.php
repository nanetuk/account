<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    'summary',
    'CONCAT(firstname," ", lastname)',
    'schedule_date',
    'schedule_time',
    'notified',
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
        if ($aColumns[$i] == 'summary') {
            $_data = '<a href="' . admin_url('schedule/edit/' . $aRow['id']) . '">' . $_data . '</a>';
            $_data .= '<div class="row-options">';
            $_data .= '<a href="' . admin_url('schedule/edit/' . $aRow['id']) . '">' . _l('view') . '</a>';

            if (has_permission('schedule', '', 'delete')) {
                $_data .= ' | <a href="' . admin_url('schedule/delete/' . $aRow['id']) . '" class="text-danger _delete">' . _l('delete') . '</a>';
            }
            $_data .= '</div>';
        } elseif ($aColumns[$i] == 'schedule_date') {
            $_data = _d($_data);
        } elseif ($aColumns[$i] == 'schedule_time') {
            $_data = format_schedule_time($_data);
        } elseif ($aColumns[$i] == 'notified') {
            $_data = $_data ? _l('yes') : _l('no');
        }
        $row[] = $_data;
    }
    $output['aaData'][] = $row;
}
