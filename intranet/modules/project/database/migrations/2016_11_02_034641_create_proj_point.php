<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Rikkei\Project\Model\ProjectPoint;

class CreateProjPoint extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('proj_point', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('project_id');
            $table->float('cost_plan_effort_current')->nullable();
            $table->float('cost_resource_allocation_current')->nullable();
            $table->float('cost_actual_effort')->nullable();
            $table->integer('qua_leakage_errors')->nullable();
            $table->integer('qua_defect_errors')->nullable();
            $table->float('tl_schedule')->nullable();
            $table->float('proc_compliance')->nullable();
            $table->float('proc_compliance_lcl')->nullable();
            $table->float('proc_compliance_target')->nullable();
            $table->float('proc_compliance_ucl')->nullable();
            $table->float('proc_report_yes')->nullable();
            $table->float('proc_report_no')->nullable();
            $table->float('proc_report_delayed')->nullable();
            $table->float('cost_lcl')->nullable();
            $table->float('cost_target')->nullable();
            $table->float('cost_ucl')->nullable();
            $table->float('cost_busy_rate_ucl')->nullable();
            $table->float('cost_busy_rate_target')->nullable();
            $table->float('cost_busy_rate_lcl')->nullable();
            $table->float('cost_effort_efficiency_lcl')->nullable();
            $table->float('cost_effort_efficiency_target')->nullable();
            $table->float('cost_effort_efficiency_ucl')->nullable();
            $table->float('tl_schedule_lcl')->nullable();
            $table->float('tl_schedule_target')->nullable();
            $table->float('tl_schedule_ucl')->nullable();
            $table->float('tl_deliver_lcl')->nullable();
            $table->float('tl_deliver_target')->nullable();
            $table->float('tl_deliver_ucl')->nullable();
            $table->float('css_css')->nullable();
            $table->float('css_css_lcl')->nullable();
            $table->float('css_css_target')->nullable();
            $table->float('css_css_ucl')->nullable();
            $table->dateTime('date_updated_css')->nullable();
            $table->float('qua_leakage_lcl')->nullable();
            $table->float('qua_leakage_target')->nullable();
            $table->float('qua_leakage_ucl')->nullable();
            $table->float('qua_defect_lcl')->nullable();
            $table->float('qua_defect_target')->nullable();
            $table->float('qua_defect_ucl')->nullable();
            $table->text('cost_billable_note')->nullable();
            $table->text('cost_plan_total_note')->nullable();
            $table->text('cost_plan_current_note')->nullable();
            $table->text('cost_resource_total_note')->nullable();
            $table->text('cost_resource_current_note')->nullable();
            $table->text('cost_actual_effort_note')->nullable();
            $table->text('cost_ees_note')->nullable();
            $table->text('cost_eey1_note')->nullable();
            $table->text('cost_eey2_note')->nullable();
            $table->text('cost_busy_rate_note')->nullable();
            $table->text('cost_productivity')->nullable();
            $table->text('qua_leakage_note')->nullable();
            $table->text('qua_defect_note')->nullable();
            $table->text('tl_schedule_note')->nullable();
            $table->text('css_css_note')->nullable();
            $table->text('css_idea_note')->nullable();
            $table->text('proc_compliance_note')->nullable();
            $table->text('proc_report_note')->nullable();
            $table->integer('position');
            $table->dateTime('report_last_at')->nullable();
            $table->tinyInteger('raise')->default(2);
            $table->index('project_id');
            $table->foreign('project_id')
                  ->references('id')
                  ->on('projs');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('proj_point');
    }
}
