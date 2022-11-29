<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterEmplProjExpersV1 extends Migration
{
    protected $tbl = 'employee_proj_expers';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable($this->tbl)) {
            return;
        }
        if (Schema::hasColumn($this->tbl, 'responsible')) {
            Schema::table($this->tbl, function (Blueprint $table) {
                $table->dropColumn('responsible');
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
    }
}
