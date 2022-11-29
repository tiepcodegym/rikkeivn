<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRoleIdColumnToLeaveDayTeamsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('leave_day_teams') || Schema::hasColumn('leave_day_teams', 'role_id')) {
            return;
        }
        Schema::table('leave_day_teams', function(Blueprint $table) {
            $table->unsignedInteger('role_id')->after('team_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (!Schema::hasTable('leave_day_teams')) {
            return;
        }
        if (!Schema::hasColumn('leave_day_teams', 'role_id')) {
            return;
        }
        Schema::table('leave_day_teams', function (Blueprint $table) {
            $table->dropColumn('role_id'); 
        });
    }
}
