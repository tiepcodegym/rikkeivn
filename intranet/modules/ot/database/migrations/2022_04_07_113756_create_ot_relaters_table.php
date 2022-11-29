<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOtRelatersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('ot_relaters')) {
            return;
        }

        Schema::create('ot_relaters', function(Blueprint $table) {
            $table->unsignedInteger('ot_register_id');
            $table->unsignedInteger('relater_id');

            $table->primary(['ot_register_id', 'relater_id']);

            $table->foreign('ot_register_id')->references('id')->on('ot_registers');
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
        Schema::drop('ot_relaters');
    }
}
