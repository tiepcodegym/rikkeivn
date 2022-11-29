<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmployeeWantJp extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('employee_want_onsite')) {
            return true;
        } 
        Schema::create('employee_want_onsite', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('employee_id');
            $table->string('place');
            $table->date('start_at')->nullable();
            $table->date('end_at')->nullable();
            $table->text('reason')->nullable();
            $table->text('note')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->index('employee_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (!Schema::hasTable('employee_want_onsite')) {
            return true;
        }
        Schema::drop('employee_want_onsite');
    }
}
