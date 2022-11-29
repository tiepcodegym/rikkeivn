<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmployeeMilitary extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasTable('employee_military')) {
            return;
        }
        Schema::create('employee_military', function (Blueprint $table) {
            $table->unsignedInteger('employee_id');
            $table->boolean('is_service_man')->nullable();
            $table->date('join_date')->nullable();
            $table->integer('position')->nullable();
            $table->integer('rank')->nullable();
            $table->integer('arm')->nullable();
            $table->string('branch')->nullable();
            $table->date('left_date')->nullable();
            $table->string('left_reason', 255)->nullable();
            $table->boolean('is_wounded_soldier')->nullable();
            $table->date('revolution_join_date')->nullable();
            $table->integer('wounded_soldier_level')->nullable();
            $table->float('num_disability_rate')->nullable();
            $table->boolean('is_martyr_regime')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->primary('employee_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('employee_military');
    }
}
