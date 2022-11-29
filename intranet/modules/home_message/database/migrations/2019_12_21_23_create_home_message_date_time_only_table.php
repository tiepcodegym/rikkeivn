<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHomeMessageDateTimeOnlyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('home_message_date_time_only', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('home_message_id');
            $table->text('date_apply');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('home_message_date_time_only');
    }
}
