<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateManageTimeTimekeepingAggregatesTable extends Migration
{
    private $table = 'manage_time_timekeeping_aggregates';
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
            $table->double('total_official_working_days', 8, 2)->nullable()->default(0);
            $table->double('total_trial_working_days', 8, 2)->nullable()->default(0);
            $table->double('total_official_ot_weekdays', 8, 2)->nullable()->default(0);
            $table->double('total_trial_ot_weekdays', 8, 2)->nullable()->default(0);
            $table->double('total_official_ot_weekends', 8, 2)->nullable()->default(0);
            $table->double('total_trial_ot_weekends', 8, 2)->nullable()->default(0);
            $table->double('total_official_ot_holidays', 8, 2)->nullable()->default(0);
            $table->double('total_trial_ot_holidays', 8, 2)->nullable()->default(0);
            $table->double('total_ot_no_salary', 8, 2)->nullable()->default(0);
            $table->unsignedInteger('total_number_late_in')->nullable()->default(0);
            $table->unsignedInteger('total_number_early_out')->nullable()->default(0);
            $table->double('total_official_business_trip', 8, 2)->nullable()->default(0);
            $table->double('total_trial_business_trip', 8, 2)->nullable()->default(0);
            $table->double('total_official_leave_day_has_salary', 8, 2)->nullable()->default(0);
            $table->double('total_trial_leave_day_has_salary', 8, 2)->nullable()->default(0);
            $table->double('total_leave_day_no_salary', 8, 2)->nullable()->default(0);
            $table->double('total_official_supplement', 8, 2)->nullable()->default(0);
            $table->double('total_trial_supplement', 8, 2)->nullable()->default(0);
            $table->double('total_official_holiay', 8, 2)->nullable()->default(0);
            $table->double('total_trial_holiay', 8, 2)->nullable()->default(0);
            $table->double('total_late_start_shift', 8, 2)->nullable()->default(0);
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();

            $table->primary(['timekeeping_table_id', 'employee_id'], 'manage_time_timekeeping_aggregates_primary');
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
        Schema::drop($this->table);
    }
}
