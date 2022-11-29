<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableRecruitmentAppointments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('recruitment_appointments')) {
            return;
        }
        Schema::create('recruitment_appointments', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('campaign_id');
            $table->unsignedInteger('apply_id')->nullable();
            $table->dateTime('start_at');
            $table->dateTime('end_at')->nullable();
            $table->text('note');
            $table->smallInteger('pass')->nullable();
            $table->dateTime('created_at');
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
            
            $table->index('campaign_id');
            $table->index('apply_id');
            $table->foreign('campaign_id')
                ->references('id')
                ->on('recruitment_campaigns');
            $table->foreign('apply_id')
                ->references('id')
                ->on('recruitment_applies');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('recruitment_appointments');
    }
}
