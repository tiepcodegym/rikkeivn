<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AddColumnExpectedDateOnProjOpCriticalsTable extends Migration
{
    private $table='proj_op_criticals';
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
            $table->dateTime('expected_date')->nullable();
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
            $table->dropColumn('expected_date');
        });
    }
}
