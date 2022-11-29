<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCheckpointTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('checkpoint', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('employee_id');
            $table->unsignedInteger('checkpoint_type_id');
            $table->string('rikker_relate');
            $table->unsignedInteger('checkpoint_time_id');
            $table->string('token');
            $table->date('start_date');
            $table->date('end_date');
            $table->timestamps();
            $table->index('employee_id');
            $table->foreign('employee_id')
                ->references('id')
                ->on('employees');
            
            $table->foreign('checkpoint_time_id')
                ->references('id')
                ->on('checkpoint_time');
            
            $table->foreign('checkpoint_type_id')
                ->references('id')
                ->on('checkpoint_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('checkpoint');
    }
}
