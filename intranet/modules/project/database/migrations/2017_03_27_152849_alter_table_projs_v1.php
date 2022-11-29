<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableProjsV1 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $projTbl = 'projs';
        if (!Schema::hasTable($projTbl)) {
            return;
        }
        if (Schema::hasColumn($projTbl, 'type_mm')) {
            return;
        }
        Schema::table($projTbl, function (Blueprint $table) {
            $table->tinyInteger('type_mm')->default(1); //1: MM, 2:MD
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $projTbl = 'projs';
        if (!Schema::hasTable($projTbl)) {
            return;
        }
        if (!Schema::hasColumn($projTbl, 'type_mm')) {
            return;
        }
        Schema::table($projTbl, function (Blueprint $table) {
            $table->dropColumn('type_mm');
        });
    }
}
