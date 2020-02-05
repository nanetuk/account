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
        $output = '<a href="' . admin_url('schedule/schedule/' . $data['result']['id']) . '">' . $data['result']['subject'] . '</a>';
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
        $achievement = $CI->schedule_model->calculate_schedule_achievement($schedule['id']);
        if ($achievement['percent'] >= 100) {
            if ($schedule['notify_when_achieve'] == 1) {
                if (date('Y-m-d') >= $schedule['end_date']) {
                    $CI->schedule_model->notify_staff_members($schedule['id'], 'success', $achievement);
                }
            }
        } else {
            // not yet achieved, check for end date
            if ($schedule['notify_when_fail'] == 1) {
                if (date('Y-m-d') > $schedule['end_date']) {
                    $CI->schedule_model->notify_staff_members($schedule['id'], 'failed', $achievement);
                }
            }
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
            'url'        => 'schedule/schedule',
            'permission' => 'schedule',
            'position'   => 56,
            ]);

    if (has_permission('schedule', '', 'view')) {
        $CI->app_menu->add_sidebar_children_item('utilities', [
                'slug'     => 'schedule-tracking',
                'name'     => _l('schedule'),
                'href'     => admin_url('schedule'),
                'position' => 24,
        ]);
    }
}


/**
 * Get schedule types for the schedule feature
 * @return array
 */
function get_schedule_types()
{
    $types = [
        [
            'key'      => 1,
            'lang_key' => 'schedule_type_total_income',
            'subtext'  => 'schedule_type_income_subtext',
        ],
        [
            'key'      => 2,
            'lang_key' => 'schedule_type_convert_leads',
        ],
        [
            'key'      => 3,
            'lang_key' => 'schedule_type_increase_customers_without_leads_conversions',
            'subtext'  => 'schedule_type_increase_customers_without_leads_conversions_subtext',
        ],
        [
            'key'      => 4,
            'lang_key' => 'schedule_type_increase_customers_with_leads_conversions',
            'subtext'  => 'schedule_type_increase_customers_with_leads_conversions_subtext',
        ],
        [
            'key'      => 5,
            'lang_key' => 'schedule_type_make_contracts_by_type_calc_database',
            'subtext'  => 'schedule_type_make_contracts_by_type_calc_database_subtext',
        ],
        [
            'key'      => 7,
            'lang_key' => 'schedule_type_make_contracts_by_type_calc_date',
            'subtext'  => 'schedule_type_make_contracts_by_type_calc_date_subtext',
        ],
        [
            'key'      => 6,
            'lang_key' => 'schedule_type_total_estimates_converted',
            'subtext'  => 'schedule_type_total_estimates_converted_subtext',
        ],
    ];

    return hooks()->apply_filters('get_schedule_types', $types);
}
/**
 * Translate schedule type based on passed key
 * @param  mixed $key
 * @return string
 */
function format_schedule_type($key)
{
    foreach (get_schedule_types() as $type) {
        if ($type['key'] == $key) {
            return _l($type['lang_key']);
        }
    }

    return $type;
}
