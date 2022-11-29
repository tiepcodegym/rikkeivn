<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableRecruitmentCampaigns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('recruitment_campaigns')) {
            return;
        }
        Schema::create('recruitment_campaigns', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code', 20);
            $table->unsignedInteger('request_id')->nullable();
            $table->string('description', 45);
            $table->dateTime('start_at')->nullable();
            $table->dateTime('end_at')->nullable();
            $table->dateTime('test_at')->nullable();
            $table->dateTime('created_at');
            $table->unsignedInteger('created_by')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
            
            $table->unique('code');
            $table->index('request_id');
            $table->foreign('request_id')
                ->references('id')
                ->on('recruitment_requests');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('recruitment_campaigns');
    }
}
