<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEmployeeNotiColumnMusicOfficeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('music_offices', function (Blueprint $table) {
            $table->dropColumn('email_noti');
            $table->integer('employee_noti')->after('sort_order')->unsigned()->nullable();
            $table->foreign('employee_noti')->references('id')->on('employees');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('music_offices', function ($table) {
            $table->string('email_noti',50)->after('sort_order')->nullable();
            $table->dropColumn('employee_noti');
        });
    }
}
