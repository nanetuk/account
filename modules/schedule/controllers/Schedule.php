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
        if (!has_permission('schedule', '', 'view')) {
            access_denied('schedule');
        }
        if ($this->input->post()) {
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
        }

        $this->load->model('staff_model');
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
