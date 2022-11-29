<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableNtestResultsAddWrittenIndex extends Migration
{
    protected $tbl = 'ntest_results';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn($this->tbl, 'written_index')) {
            Schema::table($this->tbl, function (Blueprint $table) {
                $table->text('written_index')->nullable();
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
