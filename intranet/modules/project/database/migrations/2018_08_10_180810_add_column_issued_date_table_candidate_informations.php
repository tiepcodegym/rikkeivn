<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnIssuedDateTableCandidateInformations extends Migration
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
        Schema::table($this->tbl, function(Blueprint $table)
        {
            if (!Schema::hasColumn($this->tbl, 'issued_date')) {
                $table->date('issued_date')->after('identify');
            }
            if (!Schema::hasColumn($this->tbl, 'issued_place')) {
                $table->string('issued_place')->after('issued_date');
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
        if (!Schema::hasTable($this->tbl)) {
            return;
        }
        Schema::table($this->tbl, function(Blueprint $table)
        {
            if (Schema::hasColumn($this->tbl, 'issued_date')) {
                $table->dropColumn('issued_date');
            }
            if (Schema::hasColumn($this->tbl, 'issued_place')) {
                $table->dropColumn('issued_place');
            }
        });
    }    
}
