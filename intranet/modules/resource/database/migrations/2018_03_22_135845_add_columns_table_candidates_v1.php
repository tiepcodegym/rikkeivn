<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsTableCandidatesV1 extends Migration
{
    
    protected $tbl = 'candidates';
    protected $column1 = 'calendar_id';
    protected $column2 = 'event_id';


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
        if (!Schema::hasColumn($this->tbl, $this->column1)) {
            Schema::table($this->tbl, function (Blueprint $table) {
                $table->string($this->column1)->nullable();
            });
        }
        if (!Schema::hasColumn($this->tbl, $this->column2)) {
            Schema::table($this->tbl, function (Blueprint $table) {
                $table->string($this->column2)->nullable();
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

    }
}
