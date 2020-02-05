<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                     <?php if(has_permission('schedule','','create')){ ?>
                     <div class="_buttons">
                        <a href="<?php echo admin_url('schedule/edit'); ?>" class="btn btn-info pull-left display-block"><?php echo _l('new_schedule'); ?></a>
                    </div>
                    <div class="clearfix"></div>
                    <hr class="hr-panel-heading" />
                    <?php } ?>
                    <?php render_datatable(array(
                        _l('schedule_summary'),
                        _l('staff_member'),
                        _l('schedule_date'),
                        _l('schedule_time'),
                        ),'schedule'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
    $(function(){
        initDataTable('.table-schedule', window.location.href, [4], [4]);
    });
</script>
</body>
</html>
