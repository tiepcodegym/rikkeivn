<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableNtestQuestionsAddColumnStt extends Migration
{
    protected $tbl = 'ntest_questions';
    protected $col = 'row_order';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable($this->tbl) ||
                Schema::hasColumn($this->tbl, $this->col)) {
            return;
        }
        Schema::table($this->tbl, function (Blueprint $table) {
            $table->string($this->col, 32)->nullable()->after('id');
        });
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
