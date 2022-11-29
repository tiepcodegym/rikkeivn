<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableRecruitProcess extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('recruit_process')) {
            return;
        }
        Schema::create('recruit_process', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('candidate_id');
            $table->unsignedInteger('owner');
            $table->string('action', 255);
            $table->string('note', 255);
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
            $table->foreign('candidate_id')
                ->references('id')
                ->on('candidates');
            $table->foreign('owner')
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
        Schema::drop('recruit_process');
    }
}
