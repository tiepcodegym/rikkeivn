<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterPmembers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('project_members') || 
            !Schema::hasTable('projs')
        ) {
            return;
        }
        Schema::table('project_members', function (Blueprint $table) {
            $table->dropUnique('project_members_project_id_employee_id_unique');
            $table->unique(['project_id', 'employee_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('project_members', function (Blueprint $table) {
            //
        });
    }
}
