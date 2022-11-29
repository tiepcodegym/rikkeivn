<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTestTbl extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('md_test_tests')) {
            Schema::create('md_test_tests', function(Blueprint $table) {
               $table->increments('id');
               $table->tinyInteger('type')->default(1); //1: normal, 2: gmat
               $table->string('name');
               $table->string('slug');
               $table->string('link');
               $table->integer('time')->default(20);
               $table->integer('cat_id')->unsigned()->nullable();
               $table->timestamps();
               $table->foreign('cat_id')->references('id')->on('teams')->onDelete('set null');
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
        Schema::dropIfExists('md_test_tests');
    }
}
