<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnOldCompanyTableCandidates extends Migration
{
    
    protected $tbl = 'candidates';
    protected $column = 'old_company';


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
            $table->text($this->column)->nullable();
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
