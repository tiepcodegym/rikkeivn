<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableRequestOpporMembersAddMoreColumns extends Migration
{
    protected $tbl = 'request_oppor_members';
    protected $cols = ['role', 'english_level', 'japanese_level'];

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
            foreach ($this->cols as $col) {
                if (!Schema::hasColumn($this->tbl, $col)) {
                    $table->string($col)->nullable();
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
