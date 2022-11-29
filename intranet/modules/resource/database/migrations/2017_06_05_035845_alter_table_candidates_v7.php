<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableCandidatesV7 extends Migration
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
           if (!Schema::hasColumn($this->tbl, 'skype')) {
               $table->string('skype', 50)->nullable();
           } else {
               $table->string('skype', 50)->nullable()->change();
           }
           if (!Schema::hasColumn($this->tbl, 'other_contact')) {
               $table->string('other_contact', 500)->nullable();
           } else {
               $table->string('other_contact', 500)->nullable()->change();
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
