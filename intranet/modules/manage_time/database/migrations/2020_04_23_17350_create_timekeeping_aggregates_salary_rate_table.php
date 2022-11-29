<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTimekeepingAggregatesSalaryRateTable extends Migration
{
    private $table = 'timekeeping_aggregates_salary_rate';

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
            $table->increments('id');
            $table->unsignedInteger('timekeeping_table_id');
            $table->unsignedInteger('employee_id');
            $table->unsignedInteger('tk_rate_id');
            $table->double('total_officail', 8, 2)->nullable()->default(0);
            $table->double('total_trial', 8, 2)->nullable()->default(0);
            $table->double('total_salary_officail', 8, 2)->nullable()->default(0);
            $table->double('total_salary_trial', 8, 2)->nullable()->default(0);

            $table->foreign('tk_rate_id')->references('id')->on('timekeeping_rate');
            $table->foreign('employee_id')->references('id')->on('employees');
            $table->foreign('timekeeping_table_id')->references('id')->on('manage_time_timekeeping_tables');
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop($this->table);
    }
}
