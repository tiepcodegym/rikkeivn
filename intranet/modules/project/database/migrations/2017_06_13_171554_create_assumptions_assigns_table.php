<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAssumptionsAssignsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('assumptions_assigns')) {
            return;
        }
        Schema::create('assumptions_assigns', function (Blueprint $table) {
            $table->unsignedInteger('assumption_id');
            $table->unsignedInteger('employee_id');
            $table->foreign('assumption_id')
                  ->references('id')
                  ->on('proj_op_assumptions');
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
        Schema::drop('assumptions_assigns');
    }
}
