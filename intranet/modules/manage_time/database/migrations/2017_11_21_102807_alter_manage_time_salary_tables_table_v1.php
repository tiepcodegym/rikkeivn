<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterManageTimeSalaryTablesTableV1 extends Migration
{
    private $table = 'manage_time_salary_tables';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable($this->table) || Schema::hasColumn($this->table, 'timekeeping_table_id')) {
            return;
        }
        Schema::table($this->table, function(Blueprint $table) {
            $table->unsignedInteger('timekeeping_table_id')->nullable()->after('salary_table_name');
            
            $table->foreign('timekeeping_table_id')->references('id')->on('manage_time_timekeeping_tables');
        });
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
