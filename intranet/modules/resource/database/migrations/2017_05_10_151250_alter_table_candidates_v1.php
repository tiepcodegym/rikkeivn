<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableCandidatesV1 extends Migration
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
            if (!Schema::hasColumn($this->tbl, 'identify')) {
                $table->string('identify')->nullable();
            }
            if (!Schema::hasColumn($this->tbl, 'home_town')) {
                $table->string('home_town')->nullable();
            }
            if (!Schema::hasColumn($this->tbl, 'offer_start_date')) {
                $table->date('offer_start_date')->nullable();
            }
            if (!Schema::hasColumn($this->tbl, 'had_worked')) {
                $table->string('had_worked')->nullable();
            }
            if (!Schema::hasColumn($this->tbl, 'relative_worked')) {
                $table->string('relative_worked')->nullable();
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
