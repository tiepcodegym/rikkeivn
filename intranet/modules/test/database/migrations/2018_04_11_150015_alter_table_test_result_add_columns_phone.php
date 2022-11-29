<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableTestResultAddColumnsPhone extends Migration
{
    protected $tbl = 'ntest_results';
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
            if (!Schema::hasColumn($this->tbl, 'phone')) {
                $table->string('phone', 15)->nullable()->after('employee_name');
            }
            if (!Schema::hasColumn($this->tbl, 'tester_type')) {
                $table->tinyInteger('tester_type')->default(1)->after('phone');
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
