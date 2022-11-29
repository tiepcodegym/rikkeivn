<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateComeLateRelatersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('come_late_relaters')) {
            return;
        }

        Schema::create('come_late_relaters', function(Blueprint $table) {
            $table->unsignedInteger('come_late_id');
            $table->unsignedInteger('employee_id');

            $table->primary(['come_late_id', 'employee_id']);

            $table->foreign('come_late_id')->references('id')->on('come_late_registers');
            $table->foreign('employee_id')->references('id')->on('employees');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('come_late_relaters');
    }
}
