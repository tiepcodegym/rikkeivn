<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRoleIdColumnToComeLateTeamsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('come_late_teams') || Schema::hasColumn('come_late_teams', 'role_id')) {
            return;
        }
        Schema::table('come_late_teams', function(Blueprint $table) {
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
        if (!Schema::hasTable('come_late_teams')) {
            return;
        }
        if (!Schema::hasColumn('come_late_teams', 'role_id')) {
            return;
        }
        Schema::table('come_late_teams', function (Blueprint $table) {
            $table->dropColumn('role_id'); 
        });
    }
}
