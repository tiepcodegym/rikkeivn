<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAddSomeOfOpportunityColumnInTasks extends Migration
{
    private $table='tasks';
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
            $table->tinyInteger('opportunity_source')->nullable();
            $table->tinyInteger('cost')->nullable();
            $table->tinyInteger('expected_benefit')->nullable();
            $table->text('action_plan')->nullable();
            $table->tinyInteger('action_status')->nullable();
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
            $table->dropColumn('opportunity_source');
            $table->dropColumn('cost');
            $table->dropColumn('expected_benefit');
            $table->dropColumn('action_plan');
            $table->dropColumn('action_status');
        });
    }
}
