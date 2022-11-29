<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterProjOpTrainings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('proj_op_trainings', function (Blueprint $table) {
            $table->dateTime('deleted_at')->nullable();
            $table->string('topic')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('proj_op_trainings', function (Blueprint $table) {
            //
        });
    }
}
