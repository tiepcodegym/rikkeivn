<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnIsImportantTableProjs extends Migration
{
    private $tbl = 'projs';
    private $col = 'is_important';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable($this->tbl) || Schema::hasColumn($this->tbl, $this->col)) {
            return;
        }
        Schema::table($this->tbl, function (Blueprint $table) {
            $table->boolean($this->col)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
