<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-<?php if(!isset($schedule)){echo '8 col-md-offset-2';} else {echo '6';} ?>">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin"><?php echo $title; ?></h4>
                        <hr class="hr-panel-heading" />
                        <?php echo form_open($this->uri->uri_string()); ?>
                        <?php $attrs = (isset($schedule) ? array() : array('autofocus'=>true)); ?>
                        <?php $value = (isset($schedule) ? $schedule->summary : ''); ?>
                        <?php echo render_input('summary','schedule_summary',$value,'text',$attrs); ?>
                          <?php
                           $selected = (isset($schedule) ? $schedule->staff_id : '');
                           echo render_select('staff_id',$members,array('staffid',array('firstname','lastname')),'staff_member',$selected,array('data-none-selected-text'=>_l('all_staff_members'))); ?>
                        <?php $value = (isset($schedule) ? $schedule->schedule_time : ''); ?>
                        <?php echo render_input('schedule_time','schedule_schedule_time',$value,'number'); ?>
                        <?php $value = (isset($schedule) ? _d($schedule->schedule_date) : _d(date('Y-m-d'))); ?>
                        <?php echo render_date_input('schedule_date','schedule_schedule_date',$value); ?>
                        <?php $value = (isset($schedule) ? $schedule->description : ''); ?>
                        <?php echo render_textarea('description','schedule_description',$value); ?>
                        <div class="checkbox checkbox-primary">
                            <input type="checkbox" name="notify" id="notify" <?php if(isset($schedule)){if($schedule->notify == 1){echo 'checked';} } else {echo 'checked';} ?>>
                            <label for="notify"><?php echo _l('schedule_notify'); ?></label>
                        </div>
                        <button type="submit" class="btn btn-info pull-right"><?php echo _l('submit'); ?></button>
                        <?php echo form_close(); ?>
                    </div>
                </div>
            </div>
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
    </script>
</body>
</html>
