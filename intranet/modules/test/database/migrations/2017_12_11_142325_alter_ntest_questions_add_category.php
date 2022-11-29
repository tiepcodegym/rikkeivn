<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AlterNtestQuestionsAddCategory extends Migration
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
        
        DB::statement('ALTER TABLE '. $this->tbl .' MODIFY COLUMN updated_at timestamp AFTER status');
        DB::statement('ALTER TABLE '. $this->tbl .' MODIFY COLUMN created_at timestamp AFTER status');
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
