<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTeamIdTableBusinessEmployee extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('business_trip_employees') ||
            Schema::hasColumn('business_trip_employees', 'team_id')) {
            return;
        }
        Schema::table('business_trip_employees', function (Blueprint $table) {
            $table->unsignedInteger('team_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('business_trip_employees', function (Blueprint $table) {
            $table->drop('team_id');
        });
    }
}
