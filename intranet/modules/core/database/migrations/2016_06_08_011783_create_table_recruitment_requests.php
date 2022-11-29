<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableRecruitmentRequests extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('recruitment_requests')) {
            return;
        }
        Schema::create('recruitment_requests', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('team_id');
            $table->text('description');
            $table->dateTime('end_time');
            $table->smallInteger('amount');
            $table->dateTime('created_at');
            $table->unsignedInteger('created_by')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
            
            $table->index('team_id');
            $table->foreign('team_id')
                ->references('id')
                ->on('teams');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('recruitment_requests');
    }
}
