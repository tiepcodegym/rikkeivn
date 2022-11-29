<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableNtestLangGroup extends Migration
{
    protected $tbl = 'ntest_lang_group';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable($this->tbl)) {
            Schema::create($this->tbl, function (Blueprint $table) {
                $table->unsignedInteger('group_id');
                $table->unsignedInteger('test_id');
                $table->string('lang_code', 2)->comment('vi, en, jp, ...');
                $table->primary(['group_id', 'test_id']);
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
