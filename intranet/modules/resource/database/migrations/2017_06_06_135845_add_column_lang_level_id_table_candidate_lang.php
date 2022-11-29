<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnLangLevelIdTableCandidateLang extends Migration
{
    
    protected $tbl = 'candidate_lang';
    
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable($this->tbl)) {
            return;
        }
        Schema::table($this->tbl, function (Blueprint $table) {
           if (!Schema::hasColumn($this->tbl, 'lang_level_id')) {
               $table->unsignedBigInteger('lang_level_id')->nullable();
           } else {
               $table->unsignedBigInteger('lang_level_id')->nullable()->change();
           }
           $table->foreign('lang_level_id')->references('id')->on('language_level')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
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
