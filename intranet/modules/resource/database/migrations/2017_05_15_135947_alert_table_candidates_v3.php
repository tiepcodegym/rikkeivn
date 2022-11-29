<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlertTableCandidatesV3 extends Migration
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
           if (!Schema::hasColumn($this->tbl, 'employee_id')) {
               $table->unsignedInteger('employee_id')->nullable();
               $table->foreign('employee_id')->references('id')->on('employees')->onDelete('set null');
           } 
           if (!Schema::hasColumn($this->tbl, 'gender')) {
               $table->boolean('gender');
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
