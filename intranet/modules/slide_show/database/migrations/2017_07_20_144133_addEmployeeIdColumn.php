<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEmployeeIdColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('slide_birthdays', function (Blueprint $table) {
            $table->unsignedInteger('employee_id')->after('slide_id');
            $table->foreign('employee_id')
                ->references('id')
                ->on('employees')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('slide_birthdays', function (Blueprint $table) {
            $table->dropForeign('employee_id');
            $table->dropColumn('employee_id');
        });
    }
}
