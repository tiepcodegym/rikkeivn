<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableRequestProgramming extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('request_programming')) {
            return;
        }
        Schema::create('request_programming', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('request_id');
            $table->unsignedInteger('programming_id');
            
            $table->foreign('request_id')->references('id')->on('requests');
            $table->foreign('programming_id')->references('id')->on('programming_languages');
            $table->unique(['request_id', 'programming_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('request_programming');
    }
}
