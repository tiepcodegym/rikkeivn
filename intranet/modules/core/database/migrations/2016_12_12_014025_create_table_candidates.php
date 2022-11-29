<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableCandidates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('candidates')) {
            return;
        }
        Schema::create('candidates', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('request_id');
            $table->unsignedInteger('channel_id');
            $table->string('fullname', 50);
            $table->date('birthday');
            $table->string('email');
            $table->string('mobile');
            $table->tinyInteger('position_apply');
            $table->text('university')->nullable();
            $table->text('certificate')->nullable();
            $table->tinyInteger('experience');
            $table->date('received_cv_date');
            $table->date('test_email_date')->nullable();
            $table->date('test_calling_date')->nullable();
            $table->date('test_date')->nullable();
            $table->tinyInteger('test_result')->default(0);
            $table->date('interview_email_date')->nullable();
            $table->date('interview_calling_date')->nullable();
            $table->date('interview_date')->nullable();
            $table->tinyInteger('interview_result')->default(0);
            $table->unsignedInteger('created_by')->nullable();
            $table->text('note');
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
            $table->tinyInteger('status');
            
            $table->foreign('request_id')->references('id')->on('requests');
            $table->foreign('channel_id')->references('id')->on('recruit_channel');
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
        Schema::drop('candidates');
    }
}
