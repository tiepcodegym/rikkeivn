<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableLeaveDayHistory extends Migration
{
    private $table = 'leave_day_history';
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable($this->table)) {
            return;
        }
        
        Schema::create($this->table, function (Blueprint $table) {
            $table->unsignedInteger('timekeeping_table_id');
            $table->unsignedInteger('employee_id');
            $table->float('day_added')->default(0);
            $table->timestamps();

            $table->primary(['timekeeping_table_id', 'employee_id'], 'leave_day_history_primary');
            $table->foreign('employee_id')->references('id')->on('employees');
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
        Schema::dropIfExists($this->table);
    }
}
