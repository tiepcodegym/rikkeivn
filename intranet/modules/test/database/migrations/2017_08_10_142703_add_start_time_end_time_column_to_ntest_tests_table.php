<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStartTimeEndTimeColumnToNtestTestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ntest_tests', function (Blueprint $table) {
            $table->boolean('set_valid_time');
            $table->dateTime('time_start')->nullable();
            $table->dateTime('time_end')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ntest_tests', function (Blueprint $table) {
            $table->dropColumn('set_valid_time');
            $table->dropColumn('time_start');
            $table->dropColumn('time_end');
        });
    }
}
