<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AlterTableTestsAddColumnValidViewTimeV2 extends Migration
{
    protected $tbl = 'ntest_tests';
    protected $col = 'valid_view_time';

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
                $table->boolean($this->col)->default(1);
            }
            //DB::statement('AlTER TABLE ' . $this->tbl . ' MODIFY COLUMN created_at TIMESTAMP DEFAULT "0000-00-00 00:00:00"');
            //$table->datetime('created_at')->nullable()->change();
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
