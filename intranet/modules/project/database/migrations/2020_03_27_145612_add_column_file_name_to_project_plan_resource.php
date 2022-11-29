<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnFileNameToProjectPlanResource extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('project_plan_resource')) {
            return;
        }
        Schema::table('project_plan_resource', function (Blueprint $table) {
            $table->string('file_name', 255);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (!Schema::hasTable('project_plan_resource')) {
            return;
        }
        Schema::table('project_plan_resource', function (Blueprint $table) {
            $table->dropColumn('file_name');
        });
    }
}
