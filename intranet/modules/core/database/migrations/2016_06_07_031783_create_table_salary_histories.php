<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableSalaryHistories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('salary_histories')) {
            return;
        }
        Schema::create('salary_histories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('employee_id');
            $table->date('month');
            $table->integer('total_amount')->nullable();
            $table->integer('real_amount')->nullable();
            $table->integer('tax')->nullable();
            $table->dateTime('created_at');
            $table->unsignedInteger('created_by')->nullable();
            
            $table->index('employee_id');
            $table->foreign('employee_id')
                ->references('id')
                ->on('employees');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('salary_histories');
    }
}
