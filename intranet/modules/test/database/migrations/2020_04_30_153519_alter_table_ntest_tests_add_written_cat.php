<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AlterTableNtestTestsAddWrittenCat extends Migration
{
    protected $tbl = 'ntest_tests';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable($this->tbl) || Schema::hasColumn($this->tbl, 'written_cat')) {
            return;
        }

        if (!Schema::hasColumn($this->tbl, 'written_cat')) {
            Schema::table($this->tbl, function (Blueprint $table) {
                $table->integer('written_cat')->nullable()->comment('option show written question theo category');
            });
        }
        if (Schema::hasColumn('ntest_categories', 'is_temp')) {
            Schema::table('ntest_categories', function (Blueprint $table) {
                $table->integer('is_temp')->change();
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
        //
    }
}
