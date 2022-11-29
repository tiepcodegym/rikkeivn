<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterStageAndMilestone extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('proj_op_stages', function (Blueprint $table) {
            $table->dateTime('qua_gate_plan')->nullable();
            $table->dateTime('qua_gate_actual')->nullable();
            $table->boolean('qua_gate_result');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('proj_op_stages', function (Blueprint $table) {
            //
        });
    }
}
