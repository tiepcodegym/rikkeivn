<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableCandidatesV6 extends Migration
{
    
    protected $tbl = 'candidates';
    
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
           if (!Schema::hasColumn($this->tbl, 'offer_salary_input')) {
               $table->string('offer_salary_input')->nullable();
           }
           if (Schema::hasColumn($this->tbl, 'offer_start_date')) {
               $table->string('offer_start_date')->change();
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
