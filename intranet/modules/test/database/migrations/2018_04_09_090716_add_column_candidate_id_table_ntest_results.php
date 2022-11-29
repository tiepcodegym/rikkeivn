<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddColumnCandidateIdTableNtestResults extends Migration
{
    protected $tbl = 'ntest_results';
    protected $col = 'candidate_id';

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
            if (!Schema::hasColumn($this->tbl, $this->col)) {
                $table->unsignedInteger($this->col)->nullable()->after('employee_id');
                $table->foreign($this->col)->references('id')->on('candidates')
                    ->onDelete('cascade')->onUpdate('cascade');
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
