<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableNewTestQuestionsV1 extends Migration
{
    protected $tbl = 'ntest_questions';
    
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
           if (!Schema::hasColumn($this->tbl, 'is_editor')) {
               $table->boolean('is_editor')->after('is_temp');
           } 
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
