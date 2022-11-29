<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableNtestWrittenQuestions extends Migration
{
    protected $tbl = 'ntest_written_questions';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable($this->tbl)) {
            return;
        }
        Schema::create($this->tbl, function (Blueprint $table) {
            $table->increments('id');
            $table->text('content');
            $table->integer('status')->default(1)->comment('1: inActive; 2: deActive');
            $table->unsignedInteger('test_id');
            $table->timestamps();
            $table->foreign('test_id')->references('id')->on('ntest_tests');
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
