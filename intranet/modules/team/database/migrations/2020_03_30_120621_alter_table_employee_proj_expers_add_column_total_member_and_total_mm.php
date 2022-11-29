<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableEmployeeProjExpersAddColumnTotalMemberAndTotalMm extends Migration
{
    protected $tbl = 'employee_proj_expers';
    protected $col1 = 'total_member';
    protected $col2 = 'total_mm';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable($this->tbl) || Schema::hasColumn($this->tbl, $this->col1)) {
            return;
        }
        Schema::table($this->tbl, function (Blueprint $table) {
            $table->integer($this->col1)->nullable();
            $table->float($this->col2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (!Schema::hasTable($this->tbl) || !Schema::hasColumn($this->tbl, $this->col1)) {
            return;
        }
        Schema::table($this->tbl, function (Blueprint $table) {
            $table->dropColumn($this->col1);
            $table->dropColumn($this->col2);
        });
    }
}
