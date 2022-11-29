<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCheckpointResultTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('checkpoint_result', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('checkpoint_id');
            $table->unsignedInteger('employee_id');
            $table->text('comment');
            $table->text('leader_comment');
            $table->float('total_point');
            $table->float('leader_total_point');
            $table->timestamps();
            $table->index('checkpoint_id');
            $table->foreign('checkpoint_id')
                ->references('id')
                ->on('checkpoint');
            $table->foreign('employee_id')
                ->references('id')
                ->on('employees');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('checkpoint_result');
    }
}
