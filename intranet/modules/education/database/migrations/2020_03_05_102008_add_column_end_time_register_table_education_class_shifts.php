<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnEndTimeRegisterTableEducationClassShifts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('education_class_shifts', function ($table) {
            $table->dateTime('end_time_register')->default(NULL);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('education_class_shifts', function ($table) {
            $table->dropColumn('end_time_register');
        });
    }
}
