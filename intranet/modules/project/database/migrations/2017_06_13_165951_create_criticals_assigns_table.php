<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCriticalsAssignsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('criticals_assigns')) {
            return;
        }
        Schema::create('criticals_assigns', function (Blueprint $table) {
            $table->unsignedInteger('critical_id');
            $table->unsignedInteger('employee_id');
            $table->foreign('critical_id')
                  ->references('id')
                  ->on('proj_op_criticals');
            $table->foreign('employee_id')
                  ->references('id')
                  ->on('employees');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('criticals_assigns');
    }
}
