<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableLeaveDayHistoriesAddColumnEmployeeId extends Migration
{
    protected $tbl = 'leave_day_histories';
    protected $col = 'employee_id';

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
        Schema::table($this->tbl, function (Blueprint $table) {
            $table->unsignedInteger($this->col)->nullable()->after('id');
            $table->foreign($this->col)
                    ->references('id')
                    ->on('employees')
                    ->onDelete('set null');
            $table->string('content', 1000)->change();
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
