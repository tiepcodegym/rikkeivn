<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnBranchCodeIntoTeamsTable extends Migration
{
    protected $tbl = 'teams';
    protected $col = 'branch_code';
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable($this->tbl) && !Schema::hasColumn($this->tbl, $this->col)) {
            Schema::table($this->tbl, function (Blueprint $table) {
                $table->string($this->col, 20);
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable($this->tbl) && Schema::hasColumn($this->tbl, $this->col)) {
            Schema::table($this->tbl, function (Blueprint $table) {
                $table->dropColumn($this->col);
            });
        }
    }
}
