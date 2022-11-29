<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateComeLateDayWeeksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('come_late_day_weeks')) {
            return;
        }

        Schema::create('come_late_day_weeks', function(Blueprint $table) {
            $table->unsignedInteger('come_late_id');
            $table->tinyInteger('day');

            $table->primary(['come_late_id', 'day']);

            $table->foreign('come_late_id')->references('id')->on('come_late_registers');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('come_late_day_weeks');
    }
}
