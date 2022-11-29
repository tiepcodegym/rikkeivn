<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableNtestTestsAddIsLunar extends Migration
{
    protected $tbl = 'ntest_tests';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn($this->tbl, 'is_lunar')) {
            Schema::table($this->tbl, function (Blueprint $table) {
                $table->tinyInteger('is_lunar')->default(0)->nullable();
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
