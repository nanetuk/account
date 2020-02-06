<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$schedule = [];
if (is_staff_member()) {
   $this->load->model('schedule/schedule_model');
   $schedule = $this->schedule_model->get_staff_schedule(get_staff_user_id());
}
?>
<div class="widget<?php if(count($schedule) == 0 || !is_staff_member()){echo ' hide';} ?>" id="widget-schedule">
   <?php if(is_staff_member()){ ?>
   <div class="row">
      <div class="col-md-12">
         <div class="panel_s">
            <div class="panel-body padding-10">
               <div class="widget-dragger"></div>
               <p class="padding-5">
                  <?php echo _l('schedule'); ?>
               </p>
               <hr class="hr-panel-heading-dashboard">
               <?php foreach($schedule as $schedule) { ?>
               <div class="schedule padding-5 no-padding-top">
                  <h4 class="pull-left font-medium no-mtop">
                     <small><?php echo $schedule['summary']; ?></small>
                  </h4>
                  <h4 class="pull-right bold no-mtop text-success text-right">
                     <?php echo _d($schedule['schedule_date']); ?>
                     <br>
                     <small><?php echo format_schedule_time($schedule['schedule_time']); ?></small>
                  </h4>
               </div>
               <?php } ?>
            </div>
         </div>
      </div>
   </div>
   <?php } ?>
</div>
