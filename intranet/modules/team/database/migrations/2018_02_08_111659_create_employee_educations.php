<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmployeeEducations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('employee_educations')) {
            return;
        }
        Schema::create('employee_educations', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('employee_id');
            $table->string('school')->nullable();
            $table->string('school_id')->nullable();
            $table->string('country', 50)->nullable();
            $table->string('province')->nullable();
            $table->date('start_at')->nullable();
            $table->date('end_at')->nullable();
            $table->string('faculty')->nullable();
            $table->string('majors')->nullable();
            $table->smallInteger('quality')->nullable();
            $table->tinyInteger('type')->nullable();
            $table->tinyInteger('degree')->nullable();
            $table->boolean('is_graduated')->nullable();
            $table->date('awarded_date')->nullable();
            $table->text('note')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('created_at')->nullable();
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
        Schema::drop('employee_educations');
    }
}
