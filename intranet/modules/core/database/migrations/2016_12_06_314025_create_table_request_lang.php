<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableRequestLang extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('request_lang')) {
            return;
        }
        Schema::create('request_lang', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('request_id');
            $table->unsignedInteger('lang_id');
            
            $table->foreign('request_id')->references('id')->on('requests');
            $table->foreign('lang_id')->references('id')->on('languages');
            $table->unique(['request_id', 'lang_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('request_lang');
    }
}
