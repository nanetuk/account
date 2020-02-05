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
                        <?php $value = (isset($schedule) ? $schedule->subject : ''); ?>
                        <?php echo render_input('subject','schedule_subject',$value,'text',$attrs); ?>
                        <div class="form-group select-placeholder">
                            <label for="schedule_type" class="control-label"><?php echo _l('schedule_type'); ?></label>
                            <select name="schedule_type" class="selectpicker" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                                <option value=""></option>
                                <?php foreach(get_schedule_types() as $type){ ?>
                                <option value="<?php echo $type['key']; ?>" data-subtext="<?php if(isset($type['subtext'])){echo _l($type['subtext']);} ?>" <?php if(isset($schedule) && $schedule->schedule_type == $type['key']){echo 'selected';} ?>><?php echo _l($type['lang_key']); ?></option>
                                <?php } ?>
                            </select>
                        </div>
                          <?php
                           $selected = (isset($schedule) ? $schedule->staff_id : '');
                           echo render_select('staff_id',$members,array('staffid',array('firstname','lastname')),'staff_member',$selected,array('data-none-selected-text'=>_l('all_staff_members'))); ?>
                        <?php $value = (isset($schedule) ? $schedule->achievement : ''); ?>
                        <?php echo render_input('achievement','schedule_achievement',$value,'number'); ?>
                        <?php $value = (isset($schedule) ? _d($schedule->start_date) : _d(date('Y-m-d'))); ?>
                        <?php echo render_date_input('start_date','schedule_start_date',$value); ?>
                        <?php $value = (isset($schedule) ? _d($schedule->end_date) : ''); ?>
                        <?php echo render_date_input('end_date','schedule_end_date',$value); ?>
                        <div class="hide" id="contract_types">
                            <?php $selected = (isset($schedule) ? $schedule->contract_type : ''); ?>
                            <?php echo render_select('contract_type',$contract_types,array('id','name'),'schedule_contract_type',$selected); ?>
                        </div>
                        <?php $value = (isset($schedule) ? $schedule->description : ''); ?>
                        <?php echo render_textarea('description','schedule_description',$value); ?>
                        <div class="checkbox checkbox-primary">
                            <input type="checkbox" name="notify_when_achieve" id="notify_when_achieve" <?php if(isset($schedule)){if($schedule->notify_when_achieve == 1){echo 'checked';} } else {echo 'checked';} ?>>
                            <label for="notify_when_achieve"><?php echo _l('schedule_notify_when_achieve'); ?></label>
                        </div>
                        <div class="checkbox checkbox-primary">
                            <input type="checkbox" name="notify_when_fail" id="notify_when_fail" <?php if(isset($schedule)){if($schedule->notify_when_fail == 1){echo 'checked';} } else {echo 'checked';} ?>>
                            <label for="notify_when_fail"><?php echo _l('schedule_notify_when_fail'); ?></label>
                        </div>
                        <button type="submit" class="btn btn-info pull-right"><?php echo _l('submit'); ?></button>
                        <?php echo form_close(); ?>
                    </div>
                </div>
            </div>
            <?php if(isset($schedule)){ ?>
            <div class="col-md-6">
                <div class="panel_s">
                    <div class="panel-body">
                    <h4 class="no-margin"><?php echo _l('schedule_achievement'); ?></h4>
                      <hr class="hr-panel-heading" />
                        <?php
                        $show_acchievement_ribbon = false;
                        $help_text = '';
                        if($schedule->end_date < date('Y-m-d')){
                          $achieve_indicator_class = 'danger';
                          $lang_key = 'schedule_failed';
                          $finished = true;
                          $notify_type = 'failed';

                          if($schedule->notified == 1){
                            $help_text = '<p class="text-muted text-center">'._l('schedule_staff_members_notified_about_failure').'</p>';
                        }

                        $show_acchievement_ribbon = true;
                    } else if($achievement['percent'] == 100){

                      $achieve_indicator_class = 'success';
                      $show_acchievement_ribbon = true;
                      if($schedule->notified == 1){
                        $help_text = '<p class="text-muted text-center">'._l('schedule_staff_members_notified_about_achievement').'</p>';
                    }

                    $notify_type = 'success';
                    $finished = true;
                    $lang_key = 'schedule_achieved';

                } else if($achievement['percent'] >= 80) {
                  $achieve_indicator_class = 'warning';
                  $show_acchievement_ribbon = true;
                  $lang_key = 'schedule_close';
              }
              if($show_acchievement_ribbon == true){
                  echo '<div class="ribbon '.$achieve_indicator_class.'"><span>'._l($lang_key).'</span></div>';
              }

              ?>
              <h3 class="text-center no-mtop"><?php echo _l('schedule_result_heading'); ?>
                  <small><?php echo _l('schedule_total',$achievement['total']); ?></small>
              </h3>
              <?php if($schedule->schedule_type == 1){
                echo '<p class="text-muted text-center no-mbot">' . _l('schedule_income_shown_in_base_currency') . '</p>';
            }
            if((isset($finished) && $schedule->notified == 0) && ($schedule->notify_when_achieve == 1 || $schedule->notify_when_fail == 1)){
                echo '<p class="text-center text-info">'._l('schedule_notify_when_end_date_arrives').'</p>';

                echo '<div class="text-center"><a href="'.admin_url('schedule/notify/'.$schedule->id . '/'.$notify_type).'" class="btn btn-default">'._l('schedule_notify_staff_manually').'</a></div>';
            }
            echo $help_text;
            ?>
            <div class="achievement mtop30" data-toggle="tooltip" title="<?php echo _l('schedule_total',$achievement['total']); ?>">
                <div class="schedule-progress" data-thickness="40" data-reverse="true">
                    <strong class="schedule-percent"></strong>
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
        subject: 'required',
        schedule_type: 'required',
        end_date: 'required',
        start_date: 'required',
        contract_type: {
            required: {
                depends:function(element) {
                    return $('select[name="schedule_type"]').val() == 5 || $('select[name="schedule_type"]').val() == 7;
                }
            }
        }
    });
        <?php if(isset($schedule)){ ?>
            var circle = $('.schedule-progress').circleProgress({
                value: '<?php echo $achievement['progress_bar_percent']; ?>',
                size: 250,
                fill: {
                    gradient: ["#28b8da", "#059DC1"]
                }
            }).on('circle-animation-progress', function(event, progress, stepValue) {
                $(this).find('strong.schedule-percent').html(parseInt(100 * stepValue) + '<i>%</i>');
            });
            <?php } ?>
            var schedule_type = $('select[name="schedule_type"]').val();
            if (schedule_type == 5 || schedule_type == 7) {
                $('#contract_types').removeClass('hide');
            }
            $('select[name="schedule_type"]').on('change', function() {
                var schedule_type = $(this).val();
                if (schedule_type == 5 || schedule_type == 7) {
                    $('#contract_types').removeClass('hide');
                } else {
                    $('#contract_types').addClass('hide');
                    $('#contract_type').selectpicker('val', '');
                }
            });
        });
    </script>
</body>
</html>
