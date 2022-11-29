<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnPriceUnitProjsAdditionalTable extends Migration
{
    private $table = 'projs_additional';

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
            $table->tinyInteger('unit_price');
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
            $table->dropColumn('unit_price');
        });
    }
}
