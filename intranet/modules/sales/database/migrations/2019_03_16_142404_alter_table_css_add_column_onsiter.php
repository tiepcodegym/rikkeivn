<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableCssAddColumnOnsiter extends Migration
{
    protected $tbl = 'css';
    protected $cols = ['start_onsite_date', 'end_onsite_date'];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table($this->tbl, function (Blueprint $table) {
            foreach ($this->cols as $col) {
                if (!Schema::hasColumn($this->tbl, $col)) {
                    $table->date($col)->nullable();
                }
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
