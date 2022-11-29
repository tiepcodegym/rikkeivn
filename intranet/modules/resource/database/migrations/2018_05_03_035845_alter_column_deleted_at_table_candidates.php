<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Rikkei\Resource\View\getOptions;

class AlterColumnDeletedAtTableCandidates extends Migration
{
    
    protected $tbl = 'candidates';
    protected $column = 'deleted_at';


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
           if (!Schema::hasColumn($this->tbl, $this->column)) {
               $table->datetime($this->column)->nullable();
           } else {
               $table->datetime($this->column)->nullable()->change();
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
