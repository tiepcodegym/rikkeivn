<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateManageTimeSalariesTable extends Migration
{
    private $table = 'manage_time_salaries';
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
            $table->unsignedInteger('salary_table_id');
            $table->unsignedInteger('employee_id');
            $table->double('basic_salary', 16, 2)->nullable()->default(0);
            $table->double('official_salary', 16, 2)->nullable()->default(0);
            $table->double('trial_salary', 16, 2)->nullable()->default(0);
            $table->double('overtime_salary', 16, 2)->nullable()->default(0);
            $table->double('gasoline_allowance', 16, 2)->nullable()->default(0);
            $table->double('telephone_allowance', 16, 2)->nullable()->default(0);
            $table->double('certificate_allowance', 16, 2)->nullable()->default(0);
            $table->double('bonus_and_other_allowance', 16, 2)->nullable()->default(0);
            $table->double('other_income', 16, 2)->nullable()->default(0);
            $table->double('premium_and_union', 16, 2)->nullable()->default(0);
            $table->double('advance_payment', 16, 2)->nullable()->default(0);
            $table->double('personal_income_tax', 16, 2)->nullable()->default(0);
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();

            $table->primary(['salary_table_id', 'employee_id'], 'manage_time_salaries_primary');
            $table->foreign('employee_id')->references('id')->on('employees');
            $table->foreign('salary_table_id')->references('id')->on('manage_time_salary_tables');
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
