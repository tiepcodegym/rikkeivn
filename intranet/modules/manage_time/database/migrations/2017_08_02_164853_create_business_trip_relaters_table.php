<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBusinessTripRelatersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('business_trip_relaters')) {
            return;
        }

        Schema::create('business_trip_relaters', function(Blueprint $table) {
            $table->unsignedInteger('register_id');
            $table->unsignedInteger('relater_id');

            $table->primary(['register_id', 'relater_id']);

            $table->foreign('register_id')->references('id')->on('business_trip_registers');
            $table->foreign('relater_id')->references('id')->on('employees');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('business_trip_relaters');
    }
}
