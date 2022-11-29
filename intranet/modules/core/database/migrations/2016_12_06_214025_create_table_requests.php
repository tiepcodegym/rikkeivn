<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableRequests extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('requests')) {
            return;
        }
        Schema::create('requests', function (Blueprint $table) {
            $table->increments('id');
            $table->string('content', 500);
            $table->date('request_date');
            $table->unsignedInteger('group_id')->nullable();
            $table->unsignedInteger('team_id');
            $table->unsignedInteger('saler');
            $table->unsignedInteger('interviewer');
            $table->tinyInteger('type')->nullable();
            $table->unsignedInteger('recruiter')->nullable();
            $table->string('customer', 50);
            $table->smallInteger('number_resource');
            $table->tinyInteger('role');
            $table->date('deadline');
            $table->date('start_working');
            $table->date('end_working');
            $table->tinyInteger('effort');
            $table->tinyInteger('onsite');
            $table->string('salary', 25);
            $table->text('note');
            $table->dateTime('created_at');
            $table->dateTime('updated_at')->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->tinyInteger('status');
            
            $table->foreign('group_id')->references('id')->on('request_group');
            $table->foreign('team_id')->references('id')->on('teams');
            $table->foreign('saler')->references('id')->on('employees');
            $table->foreign('interviewer')->references('id')->on('employees');
            $table->foreign('recruiter')->references('id')->on('employees');
            $table->foreign('created_by')->references('id')->on('employees');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('requests');
    }
}
