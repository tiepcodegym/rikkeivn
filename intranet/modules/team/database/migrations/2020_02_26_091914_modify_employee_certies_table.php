<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyEmployeeCertiesTable extends Migration
{
    protected $tbl = 'employee_certies';
    protected $col_date = 'confirm_date';
    protected $col_status = 'status';

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
        Schema::table($this->tbl, function (Blueprint $table) {
            if (!Schema::hasColumn($this->tbl, $this->col_date)) {
                $table->dateTime($this->col_date)->nullable();
            }
            if (!Schema::hasColumn($this->tbl, $this->col_status)) {
                $table->integer($this->col_status)->default(0);
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (!Schema::hasTable($this->tbl)) {
            return;
        }
        Schema::table($this->tbl, function (Blueprint $table) {
            if (Schema::hasColumn($this->tbl, $this->col_date)) {
                $table->dropColumn($this->col_date);
            }
            if (Schema::hasColumn($this->tbl, $this->col_status)) {
                $table->dropColumn($this->col_status);
            }
        });
    }
}
