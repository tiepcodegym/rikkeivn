<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMusicOfficeTimeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('music_office_time')) {
            return false;
        }
        Schema::create('music_office_time', function (Blueprint $table) {
            $table->integer('music_office_id')->unsigned();
            $table->foreign('music_office_id')->references('id')->on('music_offices')->onDelete('cascade');
            $table->time('time');
            $table->primary(['music_office_id', 'time']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('music_office_time');
    }
}
