<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRoleIdColumnToBusinessTripTeamsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('business_trip_teams') || Schema::hasColumn('business_trip_teams', 'role_id')) {
            return;
        }
        Schema::table('business_trip_teams', function(Blueprint $table) {
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
        if (!Schema::hasTable('business_trip_teams')) {
            return;
        }
        if (!Schema::hasColumn('business_trip_teams', 'role_id')) {
            return;
        }
        Schema::table('business_trip_teams', function (Blueprint $table) {
            $table->dropColumn('role_id'); 
        });
    }
}
