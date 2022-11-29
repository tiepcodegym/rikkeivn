<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnReasonIdTableSupplementRegisters extends Migration
{
    private $tbl = 'supplement_registers';
    private $col = 'reason_id';

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
        if (!Schema::hasColumn($this->tbl, $this->col)) {
            Schema::table($this->tbl, function(Blueprint $table) {
                $table->unsignedInteger($this->col)->nullable();
                $table->foreign($this->col)->references('id')->on('supplement_reasons');
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
