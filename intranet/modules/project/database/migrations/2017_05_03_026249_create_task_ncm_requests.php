<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTaskNcmRequests extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('task_ncm_requests', function (Blueprint $table) {
            $table->unsignedInteger('task_id');
            $table->dateTime('request_date')->nullable();
            $table->string('document')->nullable();
            $table->string('request_standard')->nullable();
            $table->unsignedInteger('requester')->nullable();
            $table->text('fix_reason')->nullable();
            $table->text('fix_content')->nullable();
            $table->tinyInteger('test_result')->nullable();
            $table->text('next_measure')->nullable();
            $table->text('evaluate_effect')->nullable();
            $table->dateTime('evaluate_date')->nullable();
            
            $table->primary('task_id');
            $table->foreign('task_id')
                  ->references('id')
                  ->on('tasks');
            $table->foreign('requester')
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
        Schema::dropIfExists('task_ncm_requests');
    }
}
