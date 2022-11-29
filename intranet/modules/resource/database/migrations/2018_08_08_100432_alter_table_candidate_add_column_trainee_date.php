<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableCandidateAddColumnTraineeDate extends Migration
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
            if (!Schema::hasColumn($this->tbl, 'trainee_start_date')) {
                $table->date('trainee_start_date')->nullable();
            }
            if (!Schema::hasColumn($this->tbl, 'trainee_end_date')) {
                $table->date('trainee_end_date')->nullable();
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
