<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableEmployeeSalary extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('employee_salary')) {
            return;
        }
        Schema::create('employee_salary', function (Blueprint $table) {
            $table->unsignedInteger('employee_id');
            $table->unsignedInteger('salary_type_id');
            $table->unsignedInteger('amount');
            $table->dateTime('created_at');
            $table->unsignedInteger('created_by')->nullable();
            $table->dateTime('updated_at')->nullable();
            
            $table->primary(['employee_id', 'salary_type_id']);
            $table->index('salary_type_id');
            $table->foreign('employee_id')
                ->references('id')
                ->on('employees');
            $table->foreign('salary_type_id')
                ->references('id')
                ->on('salary_types');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('employee_salary');
    }
}
