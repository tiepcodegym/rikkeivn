<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableCandidateAlterColumnTestGmatPoint extends Migration
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
            if (Schema::hasColumn($this->tbl, 'test_option_gmat')) {
                $table->dropColumn('test_option_gmat');
            }
            if (!Schema::hasColumn($this->tbl, 'test_gmat_point')) {
                $table->float('test_gmat_point')->nullable()->after('test_mark');
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
