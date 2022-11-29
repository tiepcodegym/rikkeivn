<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRoleIdColumnToSupplementTeamsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('supplement_teams') || Schema::hasColumn('supplement_teams', 'role_id')) {
            return;
        }
        Schema::table('supplement_teams', function(Blueprint $table) {
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
        if (!Schema::hasTable('supplement_teams')) {
            return;
        }
        if (!Schema::hasColumn('supplement_teams', 'role_id')) {
            return;
        }
        Schema::table('supplement_teams', function (Blueprint $table) {
            $table->dropColumn('role_id'); 
        });
    }
}
