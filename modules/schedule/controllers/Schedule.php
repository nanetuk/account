<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Schedule extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('schedule_model');
    }

    /* List all announcements */
    public function index()
    {
        if (!has_permission('schedule', '', 'view')) {
            access_denied('schedule');
        }
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path('schedule', 'table'));
        }
        $data['title']                 = _l('schedule_tracking');
        $this->load->view('manage', $data);
    }

    public function edit($id = '')
    {
        $this->load->model('staff_model');
        
        if (!has_permission('schedule', '', 'view')) {
            access_denied('schedule');
        }

        if ($this->input->post()) {
            if (!is_admin() && get_staff_user_id() != $this->input->post('staff_id')) {
                access_denied('schedule');
            }
            if ($id == '') {
                if (!has_permission('schedule', '', 'create')) {
                    access_denied('schedule');
                }
                $id = $this->schedule_model->add($this->input->post());
                if ($id) {
                    set_alert('success', _l('added_successfully', _l('schedule')));
                    redirect(admin_url('schedule/edit/' . $id));
                }
            } else {
                if (!has_permission('schedule', '', 'edit')) {
                    access_denied('schedule');
                }
                $success = $this->schedule_model->update($this->input->post(), $id);
                if ($success) {
                    set_alert('success', _l('updated_successfully', _l('schedule')));
                }
                redirect(admin_url('schedule/edit/' . $id));
            }
        }

        if ($id == '') {
            $title = _l('add_new', _l('schedule_lowercase'));
        } else {
            $data['schedule'] = $this->schedule_model->get($id);
            $title = _l('edit', _l('schedule_lowercase'));

            $member = $this->staff_model->get($data['schedule']->staff_id);
            if (!$member) {
                blank_page('Staff Member Not Found', 'danger');
            }

            $ts_filter_data = [];

            $data['range'] = $this->input->get('range');
            $data['period_to'] = $this->input->get('period-from');
            $data['period_from'] = $this->input->get('period-from');

            if ($this->input->get('filter')) {
                if ($this->input->get('range') != 'period') {
                    $ts_filter_data[$this->input->get('range')] = true;
                } else {
                    $ts_filter_data['period-from'] = $this->input->get('period-from');
                    $ts_filter_data['period-to'] = $this->input->get('period-to');
                }
            } else {
                $ts_filter_data['period-from'] = $data['period_from'] = $data['schedule']->schedule_date;
                $ts_filter_data['period-to'] = $data['period_to'] = $data['schedule']->schedule_date;
                $data['range'] = 'period';
            }

            $data['logged_time'] = $this->staff_model->get_logged_time_data($data['schedule']->staff_id, $ts_filter_data);
            $data['timesheets']  = $data['logged_time']['timesheets'];
            $this->load->model('currencies_model');
            $data['base_currency'] = $this->currencies_model->get_base_currency();
            $data['member'] = $member;
        }

        $data['members'] = $this->staff_model->get('', ['is_not_staff' => 0, 'active'=>1]);

        $data['title'] = $title;
        $this->load->view('schedule', $data);
    }

    /* Delete announcement from database */
    public function delete($id)
    {
        if (!has_permission('schedule', '', 'delete')) {
            access_denied('schedule');
        }
        if (!$id) {
            redirect(admin_url('schedule'));
        }
        $response = $this->schedule_model->delete($id);
        if ($response == true) {
            set_alert('success', _l('deleted', _l('schedule')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('schedule_lowercase')));
        }
        redirect(admin_url('schedule'));
    }

    public function notify($id)
    {
        if (!has_permission('schedule', '', 'edit') && !has_permission('schedule', '', 'create')) {
            access_denied('schedule');
        }
        if (!$id) {
            redirect(admin_url('schedule'));
        }
        $success = $this->schedule_model->notify_staff_members($id);
        if ($success) {
            set_alert('success', _l('schedule_notify'));
        }
        redirect(admin_url('schedule/edit/' . $id));
    }
}
