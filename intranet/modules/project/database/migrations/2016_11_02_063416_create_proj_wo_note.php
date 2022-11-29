<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProjWoNote extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('proj_wo_note', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('project_id');
            $table->text('perf_startat')->nullable();
            $table->text('perf_endat')->nullable();
            $table->text('perf_duration')->nullable();
            $table->text('perf_plan_effort')->nullable();
            $table->text('perf_effort_usage')->nullable();
            $table->text('perf_dev')->nullable();
            $table->text('perf_pm')->nullable();
            $table->text('perf_qa')->nullable();
            $table->text('qua_billable')->nullable();
            $table->text('qua_plan')->nullable();
            $table->text('qua_actual')->nullable();
            $table->text('qua_effectiveness')->nullable();
            $table->text('qua_css')->nullable();
            $table->text('qua_timeliness')->nullable();
            $table->text('qua_leakage')->nullable();
            $table->text('qua_process')->nullable();
            $table->text('qua_report')->nullable();
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
        Schema::drop('proj_wo_note');
    }
}
