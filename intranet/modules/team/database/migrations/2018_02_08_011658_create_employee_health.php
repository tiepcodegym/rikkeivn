<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmployeeHealth extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        if (Schema::hasTable('employee_health')) {
            return;
        }
        Schema::create('employee_health', function (Blueprint $table) {
            $table->unsignedInteger('employee_id');
            $table->string('blood_type', 5)->nullable();
            $table->float('height')->nullable();
            $table->float('weigth')->nullable();
            $table->string('health_status', 255)->nullable();
            $table->text('health_note')->nullable();
            $table->text('ailment')->nullable();
            $table->boolean('is_disabled')->nullable();

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
        Schema::drop('employee_health');
    }
}
