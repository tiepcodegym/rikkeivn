<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterProjPointBaselineV12 extends Migration
{
    private $table = 'proj_point_baselines';
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable($this->table)) {
            return true;
        }
        if (Schema::hasColumn($this->table, 'bl_summary_note')) {
            return true;
        }
        Schema::table($this->table, function (Blueprint $table) {
            $table->text('bl_summary_note')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
