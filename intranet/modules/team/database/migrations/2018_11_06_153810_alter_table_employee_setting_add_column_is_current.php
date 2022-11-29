<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AlterTableEmployeeSettingAddColumnIsCurrent extends Migration
{
    protected $tbl = 'employee_setting';
    protected $col = 'is_current';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable($this->tbl) || Schema::hasColumn($this->tbl, $this->col)) {
            return;
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        Schema::table($this->tbl, function (Blueprint $table) {
            $table->boolean('is_current')->default(1)->nullable()->after('value');
            $table->dropIndex(['employee_id', 'key']);
            $table->index(['employee_id', 'key', 'is_current']);
        });
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
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
