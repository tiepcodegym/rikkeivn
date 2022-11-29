<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddProjNumberToEmployeeProjExpers extends Migration
{
    protected $tbl = 'employee_proj_expers';
    protected $col = 'proj_number';
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable($this->tbl) && !Schema::hasColumn($this->tbl, $this->col)) {
            Schema::table($this->tbl, function (Blueprint $table) {
                $table->integer($this->col)->nullable();
                $table->unique(['employee_id', 'lang_code', $this->col]);
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
