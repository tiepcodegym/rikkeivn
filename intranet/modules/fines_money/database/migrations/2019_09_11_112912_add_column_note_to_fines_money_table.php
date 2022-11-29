<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnNoteToFinesMoneyTable extends Migration
{
    protected $tbl = 'fines_money';
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable($this->tbl) || Schema::hasColumn($this->tbl, 'note')) {
            return;
        }

        Schema::table($this->tbl, function (Blueprint $table) {
            $table->string('note')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (!Schema::hasTable($this->tbl) || !Schema::hasColumn($this->tbl, 'note')) {
            return;
        }

        Schema::table($this->tbl, function (Blueprint $table) {
            $table->dropColumn('note');
        });
    }
}
