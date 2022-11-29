<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AltDeliverable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('proj_deliverables', function (Blueprint $table) {
            $table->unsignedInteger('stage_id')->nullable();
            $table->foreign('stage_id')
                  ->references('id')
                  ->on('proj_op_stages');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('proj_deliverables', function (Blueprint $table) {
            //
        });
    }
}
