<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnIdToFinesActionHistory extends Migration
{
    protected $tbl = 'fines_action_history';
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable($this->tbl) || Schema::hasColumn($this->tbl, 'id')) {
            return;
        }
        Schema::table($this->tbl, function (Blueprint $table) {
            $table->increments('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable($this->tbl) && Schema::hasColumn($this->tbl, 'id')) {
            Schema::table($this->tbl, function (Blueprint $table) {
                $table->dropColumn('id');
            });
        }
    }
}
