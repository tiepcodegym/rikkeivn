<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class ChangeColumnDoubleTypeInProjPointBaselinesTable extends Migration
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
            $table->string('proc_compliance')->change();
            $table->string('css_css')->change();
            $table->string('cost_actual_effort')->change();
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
            $table->dropColumn('proc_compliance');
            $table->dropColumn('css_css');
            $table->dropColumn('cost_actual_effort');
        });
    }
}
