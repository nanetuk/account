<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Schedule
Description: Work schedule for employees
Version: 2.3.0
Requires at least: 2.3.*
*/

define('SCHEDULE_MODULE_NAME', 'schedule');

hooks()->add_action('after_cron_run', 'schedule_notification');
hooks()->add_action('admin_init', 'schedule_module_init_menu_items');
hooks()->add_action('staff_member_deleted', 'schedule_staff_member_deleted');
hooks()->add_action('admin_init', 'schedule_permissions');

hooks()->add_filter('migration_tables_to_replace_old_links', 'schedule_migration_tables_to_replace_old_links');
hooks()->add_filter('global_search_result_query', 'schedule_global_search_result_query', 10, 3);
hooks()->add_filter('global_search_result_output', 'schedule_global_search_result_output', 10, 2);
hooks()->add_filter('get_dashboard_widgets', 'schedule_add_dashboard_widget');

function schedule_add_dashboard_widget($widgets)
{
    $widgets[] = [
            'path'      => 'schedule/widget',
            'container' => 'right-4',
        ];

    return $widgets;
}

function schedule_staff_member_deleted($data)
{
    $CI = &get_instance();
    $CI->db->where('staff_id', $data['id']);
    $CI->db->update(db_prefix() . 'schedule', [
            'staff_id' => $data['transfer_data_to'],
        ]);
}

function schedule_global_search_result_output($output, $data)
{
    if ($data['type'] == 'schedule') {
        $output = '<a href="' . admin_url('schedule/edit/' . $data['result']['id']) . '">' . $data['result']['subject'] . '</a>';
    }

    return $output;
}

function schedule_global_search_result_query($result, $q, $limit)
{
    $CI = &get_instance();
    if (has_permission('schedule', '', 'view')) {
        // Schedule
        $CI->db->select()->from(db_prefix() . 'schedule')->like('description', $q)->or_like('subject', $q)->limit($limit);

        $CI->db->order_by('subject', 'ASC');

        $result[] = [
                'result'         => $CI->db->get()->result_array(),
                'type'           => 'schedule',
                'search_heading' => _l('schedule'),
            ];
    }

    return $result;
}

function schedule_migration_tables_to_replace_old_links($tables)
{
    $tables[] = [
                'table' => db_prefix() . 'schedule',
                'field' => 'description',
            ];

    return $tables;
}

function schedule_permissions()
{
    $capabilities = [];

    $capabilities['capabilities'] = [
            'view'   => _l('permission_view') . '(' . _l('permission_global') . ')',
            'create' => _l('permission_create'),
            'edit'   => _l('permission_edit'),
            'delete' => _l('permission_delete'),
    ];

    register_staff_capabilities('schedule', $capabilities, _l('schedule'));
}

function schedule_notification()
{
    $CI = &get_instance();
    $CI->load->model('schedule/schedule_model');
    $schedule = $CI->schedule_model->get('', true);
    foreach ($schedule as $schedule) {
        if (date('Y-m-d') > $schedule['schedule_date']) {
            $CI->schedule_model->notify_staff_members($schedule['id']);
        }
    }
}

/**
* Register activation module hook
*/
register_activation_hook(SCHEDULE_MODULE_NAME, 'schedule_module_activation_hook');

function schedule_module_activation_hook()
{
    $CI = &get_instance();
    require_once(__DIR__ . '/install.php');
}

/**
* Register language files, must be registered if the module is using languages
*/
register_language_files(SCHEDULE_MODULE_NAME, [SCHEDULE_MODULE_NAME]);

/**
 * Init schedule module menu items in setup in admin_init hook
 * @return null
 */
function schedule_module_init_menu_items()
{
    $CI = &get_instance();

    $CI->app->add_quick_actions_link([
            'name'       => _l('schedule'),
            'url'        => 'schedule/edit',
            'permission' => 'schedule',
            'position'   => 56,
            ]);

    if (has_permission('schedule', '', 'view')) {
        $CI->app_menu->add_sidebar_menu_item('schedule', [
                'name'     => _l('schedule'),
                'href'     => admin_url('schedule'),
                'position' => 2,
                'icon'     => 'fa fa-calendar',
        ]);
    }
}

/**
 * Translate schedule time based on seconds
 * @param  mixed $seconds
 * @return string
 */
function format_schedule_time($seconds)
{
    return gmdate("H:i:s", $seconds * 60);
}
