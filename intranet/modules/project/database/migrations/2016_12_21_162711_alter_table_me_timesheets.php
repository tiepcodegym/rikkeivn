<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableMeTimesheets extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('me_timesheets')) {
            if (!Schema::hasColumn('me_timesheets','employee_email')) {
                Schema::table('me_timesheets', function ($table) {
                    $table->string('employee_email')->nullable()->after('employee_code');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
