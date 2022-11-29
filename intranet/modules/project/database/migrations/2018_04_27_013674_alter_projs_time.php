<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterProjsTime extends Migration
{
    protected $tbl = 'projs';
    protected $tblM = 'project_members';
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable($this->tbl)) {
            Schema::table($this->tbl, function (Blueprint $table) {
                $table->date('start_at')->nullable()->change(); 
                $table->date('end_at')->nullable()->change();
            });
        }
        if (Schema::hasTable($this->tblM)) {
            Schema::table($this->tblM, function (Blueprint $table) {
                $table->date('start_at')->nullable()->change(); 
                $table->date('end_at')->nullable()->change();
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
