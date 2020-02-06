<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-<?php if(!isset($schedule)){echo '8 col-md-offset-2';} else {echo '12';} ?>">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin"><?php echo $title; ?></h4>
                        <hr class="hr-panel-heading" />
                        <?php echo form_open($this->uri->uri_string()); ?>
                        <?php $attrs = (isset($schedule) ? array() : array('autofocus'=>true)); ?>
                        <?php $value = (isset($schedule) ? $schedule->summary : ''); ?>
                        <?php echo render_input('summary','schedule_summary',$value,'text',$attrs); ?>
                        <?php $selected = (isset($schedule) ? $schedule->staff_id : ''); ?>
                        <?php echo render_select('staff_id',$members,array('staffid',array('firstname','lastname')),'staff_member',$selected); ?>
                        <?php $value = (isset($schedule) ? _d($schedule->schedule_date) : _d(date('Y-m-d'))); ?>
                        <?php echo render_date_input('schedule_date','schedule_date',$value); ?>
                        <?php $value = (isset($schedule) ? $schedule->schedule_time : ''); ?>
                        <?php echo render_input('schedule_time','schedule_time',$value,'number'); ?>
                        <?php $value = (isset($schedule) ? $schedule->description : ''); ?>
                        <?php echo render_textarea('description','schedule_description',$value); ?>
                        <button type="submit" class="btn btn-info pull-right"><?php echo _l('submit'); ?></button>
                        <?php echo form_close(); ?>
                    </div>
                </div>
            </div>
            <?php if ($schedule) { ?>
            <div class="col-md-6">
                <div class="panel_s">
                    <div class="panel-body">
                    <h4 class="no-margin">
                        <?php echo _l('task_timesheets'); ?>
                    </h4>
                    <hr class="hr-panel-heading" />
                    <?php echo form_open($this->uri->uri_string(),array('method'=>'GET')); ?>
                    <?php echo form_hidden('filter','true'); ?>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="select-placeholder">
                                <select name="range" id="range" class="selectpicker" data-width="100%">
                                <option value="this_month" <?php if(!$range || $range == 'this_month'){echo 'selected';} ?>><?php echo _l('staff_stats_this_month_total_logged_time'); ?></option>
                                <option value="last_month" <?php if($range == 'last_month'){echo 'selected';} ?>><?php echo _l('staff_stats_last_month_total_logged_time'); ?></option>
                                <option value="this_week" <?php if($range == 'this_week'){echo 'selected';} ?>><?php echo _l('staff_stats_this_week_total_logged_time'); ?></option>
                                <option value="last_week" <?php if($range == 'last_week'){echo 'selected';} ?>><?php echo _l('staff_stats_last_week_total_logged_time'); ?></option>
                                <option value="period" <?php if($range == 'period'){echo 'selected';} ?>><?php echo _l('period_datepicker'); ?></option>
                                </select>
                            </div>
                            <div class="row mtop15">
                                <div class="col-md-12 period <?php if($range != 'period'){echo 'hide';} ?>">
                                <?php echo render_date_input('period-from','',$period_from); ?>
                                </div>
                                <div class="col-md-12 period <?php if($range != 'period'){echo 'hide';} ?>">
                                <?php echo render_date_input('period-to','',$period_to); ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2 text-right">
                            <button type="submit" class="btn btn-success apply-timesheets-filters"><?php echo _l('apply'); ?></button>
                        </div>
                    </div>
                    <?php echo form_close(); ?>
                    <hr class="hr-panel-heading" />
                        <table class="table dt-table scroll-responsive">
                            <thead>
                                <th><?php echo _l('task'); ?></th>
                                <th><?php echo _l('timesheet_start_time'); ?></th>
                                <th><?php echo _l('timesheet_end_time'); ?></th>
                                <th><?php echo _l('task_relation'); ?></th>
                                <th><?php echo _l('staff_hourly_rate'); ?> (<?php echo _l('als_staff'); ?>)</th>
                                <th><?php echo _l('time_h'); ?></th>
                                <th><?php echo _l('time_decimal'); ?></th>
                                <th data-sortable="false"></th>
                            </thead>
                            <tbody>
                                <?php
                                $total_logged_time = 0;
                                foreach($timesheets as $t){ ?>
                                <tr>
                                <td><a href="#" onclick="init_task_modal(<?php echo $t['task_id']; ?>); return false;"><?php echo $t['name']; ?></a></td>
                                <td data-order="<?php echo $t['start_time']; ?>"><?php echo _dt($t['start_time'], true); ?></td>
                                <td data-order="<?php echo $t['end_time']; ?>">
                                    <?php
                                        // Allow admins or timer user to stop forgotten timers by staff member
                                        if($t['not_finished'] && (is_admin() || $t['staff_id'] === get_staff_user_id())) {
                                            ?>
                                                <a href="#"
                                                <?php
                                                // Do not show the note popover when there is no associated task
                                                // The user will be able to add note and select task in the popup window that will open
                                                if($t['task_id'] != 0){ ?>
                                                data-toggle="popover"
                                                data-placement="bottom"
                                                data-html="true"
                                                data-trigger="manual"
                                                data-title="<?php echo _l('note'); ?>"
                                                data-content='<?php echo render_textarea('timesheet_note'); ?><button type="button"
                                                onclick="timer_action(this, <?php echo $t['task_id']; ?>, <?php echo $t['id']; ?>, 1);" class="btn btn-info btn-xs"><?php echo _l('save'); ?></button>'
                                                onclick="return false;"
                                                <?php } else { ?>
                                                onclick="timer_action(this, <?php echo $t['task_id']; ?>, <?php echo $t['id']; ?>, 1); return false;"
                                                <?php } ?>
                                                class="text-danger"
                                                >
                                                <i class="fa fa-clock-o"></i>
                                                <?php echo _l('task_stop_timer'); ?>
                                                </a>
                                            <?php
                                        } else if($t['not_finished']) {
                                            echo '<b>' . _l('timer_not_stopped_yet') . '</b>';
                                        } else {
                                            echo _dt($t['end_time'], true);
                                        }
                                    ?>
                                </td>
                                <td>
                                    <?php
                                        $rel_data   = get_relation_data($t['rel_type'], $t['rel_id']);
                                        $rel_values = get_relation_values($rel_data, $t['rel_type']);
                                        echo '<a href="' . $rel_values['link'] . '">' . $rel_values['name'].'</a>';
                                        ?>
                                </td>
                                <td><?php echo app_format_money($t['hourly_rate'], $base_currency); ?></td>
                                <td>
                                    <?php echo '<b>'.seconds_to_time_format($t['end_time'] - $t['start_time']).'</b>'; ?>
                                </td>
                                <td data-order="<?php echo sec2qty($t['total']); ?>">
                                    <?php
                                        $total_logged_time += $t['total'];
                                        echo '<b>'.sec2qty($t['total']).'</b>';
                                        ?>
                                </td>
                                <td>
                                    <?php
                                        if(!$t['billed']){
                                            if(has_permission('tasks','','delete')
                                            || (has_permission('projects','','delete') && $t['rel_type'] == 'project')
                                            || $t['staff_id'] == get_staff_user_id()){
                                                echo '<a href="'.admin_url('tasks/delete_timesheet/'.$t['id']).'" class="pull-right text-danger mtop5"><i class="fa fa-remove"></i></a>';
                                            }
                                        }
                                    ?>
                                </td>
                                </tr>
                                <?php } ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td align="right"><?php echo '<b>' . _l('total_by_hourly_rate') .':</b> '. app_format_money((sec2qty($total_logged_time) * $member->hourly_rate), $base_currency); ?></td>
                                <td align="right">
                                    <?php echo '<b>'._l('total_logged_hours_by_staff') . ':</b> ' . seconds_to_time_format($total_logged_time); ?>
                                </td>
                                <td align="right">
                                    <?php echo '<b>'._l('total_logged_hours_by_staff') . ':</b> ' . sec2qty($total_logged_time); ?>
                                </td>
                                <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            <?php } ?>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
    $(function(){
       appValidateForm($('form'), {
            summary: 'required',
            staff_id: 'required',
            schedule_date: 'required',
            schedule_time: 'required'
        });
    });
    </script>
</body>
</html>
