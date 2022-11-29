<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnProgrammingLanguagesTableCandidates extends Migration
{

    protected $tbl = 'candidates';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table($this->tbl, function (Blueprint $table) {
            $table->unsignedInteger('programming_language_id')->nullable();
            $table->foreign('programming_language_id')->references('id')->on('programming_languages');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table($this->tbl, function (Blueprint $table) {
            $table->dropColumn('programming_language_id');
        });
    }

}
