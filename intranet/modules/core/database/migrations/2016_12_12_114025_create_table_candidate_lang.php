<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableCandidateLang extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('candidate_lang')) {
            return;
        }
        Schema::create('candidate_lang', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('candidate_id');
            $table->unsignedInteger('lang_id');
            $table->foreign('candidate_id')->references('id')->on('candidates');
            $table->foreign('lang_id')->references('id')->on('languages');
            $table->unique(['candidate_id', 'lang_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('candidate_lang');
    }
}
