<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnEvaluatorIdTableCheckpoint extends Migration
{
    private $table = 'checkpoint';
    
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table($this->table, function($table) {
            $table->integer('evaluator_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table($this->table, function($table) {
            $table->dropColumn('evaluator_id');
        });
    }
}
