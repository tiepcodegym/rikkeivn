<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnIsWorkingIntoTeamMembers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('employee_team_history', function (Blueprint $table) {
            $table->boolean('is_working')->default(false)->comment = 'team where the employee is working';
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('employee_team_history', function (Blueprint $table) {
            $table->dropColumn('is_working');
        });
    }
}
