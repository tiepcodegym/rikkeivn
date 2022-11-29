<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableRepeatSlider extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('repeat_slider', function (Blueprint $table) {
            $table->increments('id');
            $table->tinyInteger('type');
            $table->unsignedInteger('slide_id');
            $table->index('slide_id');
            $table->foreign('slide_id')
                ->references('id')
                ->on('slide');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('repeat_slider');
    }
}
