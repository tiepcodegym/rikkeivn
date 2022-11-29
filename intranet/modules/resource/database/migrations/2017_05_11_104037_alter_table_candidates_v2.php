<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableCandidatesV2 extends Migration
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
            if (!Schema::hasColumn($this->tbl, 'test_option_gmat')) {
                $table->boolean('test_option_gmat');
            }
            if (!Schema::hasColumn($this->tbl, 'test_option_type_id')) {
                $table->unsignedInteger('test_option_type_id')->nullable();
                $table->foreign('test_option_type_id')->references('id')->on('ntest_types')->onDelete('set null');
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
