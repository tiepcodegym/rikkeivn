<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableNtestTypesMeta extends Migration
{
    protected $tbl = 'ntest_types_meta';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable($this->tbl)) {
            Schema::create($this->tbl, function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('type_id');
                $table->string('name');
                $table->string('lang_code', 2)->comment('vi, en, jp, ...');
                $table->foreign('type_id')->references('id')->on('ntest_types')->onDelete('cascade');
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
