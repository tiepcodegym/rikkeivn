<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AddColumnCorrectionCostInProjPointBaselines extends Migration
{
    protected $table = 'proj_point_baselines';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable($this->table)) {
            return;
        }
        Schema::table($this->table, function (Blueprint $table) {
            $table->string('correct_cost_lcl')->nullable();
            $table->string('correct_cost_target')->nullable();
            $table->string('correct_cost_ucl')->nullable();
            $table->string('correction_cost')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (!Schema::hasTable($this->table)) {
            return;
        }
        Schema::table($this->table, function (Blueprint $table) {
            $table->dropColumn('correct_cost_lcl');
            $table->dropColumn('correct_cost_target');
            $table->dropColumn('correct_cost_ucl');
            $table->dropColumn('correction_cost');
        });
    }
}
