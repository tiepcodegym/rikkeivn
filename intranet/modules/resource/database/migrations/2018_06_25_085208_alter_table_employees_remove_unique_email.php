<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AlterTableEmployeesRemoveUniqueEmail extends Migration
{
    protected $tbl = 'employees';
    protected $tblfk = 'hr_weekly_report_note';

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

        $hasTblFk = false;
        if (Schema::hasTable($this->tblfk) && Schema::hasColumn($this->tblfk, 'email')) {
            Schema::table($this->tblfk, function (Blueprint $table) {
                $table->dropForeign($this->tblfk . '_email_foreign');
            });
            $hasTblFk = true;
        }
        Schema::table($this->tbl, function (Blueprint $table) {
            $table->dropUnique($this->tbl . '_email_unique');
            $table->index(['email', 'deleted_at']);
        });
        if ($hasTblFk) {
            Schema::table($this->tblfk, function (Blueprint $table) {
                $table->foreign('email')
                    ->references('email')
                    ->on('employees')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
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
        //
    }
}
