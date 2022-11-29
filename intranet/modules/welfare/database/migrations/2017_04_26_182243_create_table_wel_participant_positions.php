<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableWelParticipantPositions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('wel_participant_positions')) {
            return;
        }
        Schema::create('wel_participant_positions', function (Blueprint $table) {
            $table->integer('wel_id')->unsigned();
            $table->integer('role_id')->unsigned();

            $table->foreign('wel_id')->references('id')->on('welfares');
            $table->foreign('role_id')->references('id')->on('roles');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('wel_participant_positions');
    }
}
