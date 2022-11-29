<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Rikkei\Project\Model\ProjectPoint;

class CreateTableProjPointBaseline extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('proj_point_baselines')) {
            return;
        }
        Schema::create('proj_point_baselines', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('project_id');
            
            $table->float('cost_billable_effort')->nullable();
            $table->float('cost_plan_effort_total')->nullable();
            $table->float('cost_plan_effort_total_point')->nullable();
            $table->float('cost_plan_effort_current')->nullable();
            $table->float('cost_resource_allocation_total')->nullable();
            $table->float('cost_resource_allocation_current')->nullable();
            $table->float('cost_actual_effort')->nullable();            
            $table->float('cost_effort_effectiveness')->nullable();
            $table->float('cost_effort_effectiveness_point')->nullable();
            $table->float('cost_effort_efficiency1')->nullable();
            $table->float('cost_effort_efficiency2')->nullable();
            $table->float('cost_effort_efficiency2_point')->nullable();
            $table->float('cost_busy_rate')->nullable();
            $table->float('cost_busy_rate_point')->nullable();
            $table->text('cost_productivity')->nullable();
            $table->float('cost_lcl')->nullable();
            $table->float('cost_target')->nullable();
            $table->float('cost_ucl')->nullable();
            $table->float('cost_busy_rate_ucl')->nullable();
            $table->float('cost_busy_rate_target')->nullable();
            $table->float('cost_busy_rate_lcl')->nullable();
            $table->float('cost_effort_efficiency_lcl')->nullable();
            $table->float('cost_effort_efficiency_target')->nullable();
            $table->float('cost_effort_efficiency_ucl')->nullable();
            $table->integer('qua_leakage_errors')->nullable();
            $table->integer('qua_defect_errors')->nullable();
            $table->float('qua_leakage')->nullable();
            $table->float('qua_leakage_point')->nullable();
            $table->float('qua_defect')->nullable();
            $table->float('qua_defect_point')->nullable();
            $table->float('qua_leakage_lcl')->nullable();
            $table->float('qua_leakage_target')->nullable();
            $table->float('qua_leakage_ucl')->nullable();
            $table->float('qua_defect_lcl')->nullable();
            $table->float('qua_defect_target')->nullable();
            $table->float('qua_defect_ucl')->nullable();
            
            $table->float('tl_schedule')->nullable();
            $table->float('tl_schedule_point')->nullable();
            $table->float('tl_deliver')->nullable();
            $table->float('tl_deliver_point')->nullable();
            $table->float('tl_schedule_lcl')->nullable();
            $table->float('tl_schedule_target')->nullable();
            $table->float('tl_schedule_ucl')->nullable();
            $table->float('tl_deliver_lcl')->nullable();
            $table->float('tl_deliver_target')->nullable();
            $table->float('tl_deliver_ucl')->nullable();
            
            $table->float('proc_compliance')->nullable();
            $table->float('proc_compliance_point')->nullable();
            $table->float('proc_report')->nullable();
            $table->float('proc_report_point')->nullable();
            $table->integer('proc_report_yes')->nullable();
            $table->integer('proc_report_no')->nullable();
            $table->integer('proc_report_delayed')->nullable();
            $table->float('proc_compliance_lcl')->nullable();
            $table->float('proc_compliance_target')->nullable();
            $table->float('proc_compliance_ucl')->nullable();
            
            $table->float('css_css')->nullable();
            $table->float('css_css_point')->nullable();
            $table->integer('css_ci')->nullable();
            $table->integer('css_ci_point')->nullable();
            $table->integer('css_ci_negative')->nullable();
            $table->integer('css_ci_positive')->nullable();
            $table->float('css_css_lcl')->nullable();
            $table->float('css_css_target')->nullable();
            $table->float('css_css_ucl')->nullable();
            
            $table->tinyInteger('summary')->default(ProjectPoint::COLOR_STATUS_WHITE);
            $table->tinyInteger('cost')->default(ProjectPoint::COLOR_STATUS_WHITE);
            $table->tinyInteger('quality')->default(ProjectPoint::COLOR_STATUS_WHITE);
            $table->tinyInteger('tl')->default(ProjectPoint::COLOR_STATUS_WHITE);
            $table->tinyInteger('proc')->default(ProjectPoint::COLOR_STATUS_WHITE);
            $table->tinyInteger('css')->default(ProjectPoint::COLOR_STATUS_WHITE);
            $table->float('point_total')->default(0);
            $table->smallInteger('project_evaluation')->default(0);
            $table->tinyInteger('raise')->default(2);
            $table->dateTime('first_report')->nullable();
            $table->integer('position');
            $table->integer('changed_by')->unsigned()->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            
            $table->index('project_id');
            $table->foreign('project_id')
                  ->references('id')
                  ->on('projs');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('proj_point_baselines');
    }
}
