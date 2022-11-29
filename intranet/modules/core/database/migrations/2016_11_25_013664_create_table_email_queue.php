<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableEmailQueue extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('email_queues')) {
            return;
        }
        Schema::create('email_queues', function (Blueprint $table) {
            $table->increments('id');
            $table->string('from_email');
            $table->string('from_name')->nullable();
            $table->string('to_email');
            $table->string('to_name')->nullable();
            $table->string('subject');
            $table->string('template_name');
            $table->text('template_data')->nullable();
            $table->text('option')->nullable();
            $table->dateTime('created_at');
            $table->dateTime('send_at')->nullable();
            $table->dateTime('sent_plan')->nullable();
            $table->string('error')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('email_queues');
    }
}
