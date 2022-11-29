<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnSetMinPointToNtestTestsTable extends Migration
{
    protected $tbl = 'ntest_tests';
    protected $col1 = 'set_min_point';
    protected $col2 = 'min_point';
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable($this->tbl)) {
            Schema::table($this->tbl, function (Blueprint $table) {
                if (!Schema::hasColumn($this->tbl, $this->col1)) {
                    $table->boolean($this->col1)->default(0);
                }
                if (!Schema::hasColumn($this->tbl, $this->col2)) {
                    $table->integer($this->col2)->nullable();
                }
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
        if (Schema::hasTable($this->tbl)) {
            Schema::table($this->tbl, function (Blueprint $table) {
                if (Schema::hasColumn($this->tbl, $this->col1)) {
                    $table->dropColumn($this->col1);
                }
                if (Schema::hasColumn($this->tbl, $this->col2)) {
                    $table->dropColumn($this->col2);
                }
            });
        }
    }
}
