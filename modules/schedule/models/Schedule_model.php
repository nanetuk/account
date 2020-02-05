<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Schedule_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param  integer (optional)
     * @return object
     * Get single schedule
     */
    public function get($id = '', $exclude_notified = false)
    {
        if (is_numeric($id)) {
            $this->db->where('id', $id);

            return $this->db->get(db_prefix() . 'schedule')->row();
        }
        if ($exclude_notified == true) {
            $this->db->where('notified', 0);
        }

        return $this->db->get(db_prefix() . 'schedule')->result_array();
    }

    public function get_staff_schedule($staff_id, $exclude_notified = true)
    {
        $this->db->where('staff_id', $staff_id);
        if ($exclude_notified) {
            $this->db->where('notified', 0);
        }

        $this->db->order_by('schedule_date', 'asc');
        $schedule = $this->db->get(db_prefix() . 'schedule')->result_array();

        return $schedule;
    }

    /**
     * Add new schedule
     * @param mixed $data All $_POST dat
     * @return mixed
     */
    public function add($data)
    {
        $data['staff_id']      = $data['staff_id'] == '' ? 0 : $data['staff_id'];
        $data['schedule_date'] = to_sql_date($data['schedule_date']);

        $this->db->insert(db_prefix() . 'schedule', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            log_activity('New Schedule Added [ID:' . $insert_id . ']');

            return $insert_id;
        }

        return false;
    }

    /**
     * Update schedule
     * @param  mixed $data All $_POST data
     * @param  mixed $id   schedule id
     * @return boolean
     */
    public function update($data, $id)
    {
        $data['staff_id']      = $data['staff_id'] == '' ? 0 : $data['staff_id'];
        $data['schedule_date'] = to_sql_date($data['schedule_date']);

        $schedule = $this->get($id);

        if ($schedule->notified == 1 && date('Y-m-d') < $data['schedule_date']) {
            // After schedule finished, user changed/extended date? If yes, set this schedule to be notified
            $data['notified'] = 0;
        }

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'schedule', $data);
        if ($this->db->affected_rows() > 0) {
            log_activity('Schedule Updated [ID:' . $id . ']');

            return true;
        }

        return false;
    }

    /**
     * Delete schedule
     * @param  mixed $id schedule id
     * @return boolean
     */
    public function delete($id)
    {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'schedule');
        if ($this->db->affected_rows() > 0) {
            log_activity('Schedule Deleted [ID:' . $id . ']');

            return true;
        }

        return false;
    }

    /**
     * Notify staff members about schedule result
     * @param  mixed $id          schedule id
     * @return boolean
     */
    public function notify_staff_members($id)
    {
        $schedule = $this->get($id);

        $this->db->where('active', 1)->where('staffid', 1);
        $staff = $this->db->get(db_prefix() . 'staff')->result_array();

        $notifiedUsers = [];
        $notified = add_notification([
            'fromcompany'     => 1,
            'touserid'        => 1,
            'description'     => $schedule_desc,
            'additional_data' => serialize([
                format_schedule_type($schedule->schedule_type),
                $schedule->achievement,
                $achievement['total'],
                _d($schedule->start_date),
                _d($schedule->end_date),
            ]),
        ]);
        if ($notified) {
            array_push($notifiedUsers, 1);
        }

        pusher_trigger_notification($notifiedUsers);
        $this->db->where('id', $schedule->id);
        $this->db->update(db_prefix() . 'schedule', [
            'notified' => 1,
        ]);

        if (count($staff) > 0 && $this->db->affected_rows() > 0) {
            return true;
        }

        return false;
    }
}
