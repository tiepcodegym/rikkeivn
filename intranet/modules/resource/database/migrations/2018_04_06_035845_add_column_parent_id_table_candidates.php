<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnParentIdTableCandidates extends Migration
{
    
    protected $tbl = 'candidates';
    protected $column = 'parent_id';


    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable($this->tbl) || Schema::hasColumn($this->tbl, $this->column)) {
            return;
        }
        Schema::table($this->tbl, function (Blueprint $table) {
            $table->unsignedInteger($this->column)->nullable();
            $table->foreign($this->column)->references('id')->on($this->tbl);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable($this->tbl) && Schema::hasColumn($this->tbl, $this->column)) {
            Schema::table($this->tbl, function (Blueprint $table) {
                $table->dropColumn($this->column);
            });
        }
    }
}
