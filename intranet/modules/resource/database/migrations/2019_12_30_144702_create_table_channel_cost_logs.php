<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableChannelCostLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('channel_cost_logs')) {
            return;
        }

        Schema::create('channel_cost_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('candidate_id');
            $table->integer('channel_id');
            $table->bigInteger('cost');
            $table->date('working_date');
            $table->timestamps();
            $table->datetime('deleted_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('channel_cost_logs');
    }
}
