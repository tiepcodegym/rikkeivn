<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSalaryRateTable extends Migration
{
    private $table = 'salary_rate';

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
            $table->unsignedInteger('employee_id');
            $table->unsignedInteger('employee_id_upfile');
            $table->date('date');
            $table->unsignedInteger('tk_rate_id');

            $table->foreign('tk_rate_id')->references('id')->on('timekeeping_rate');
            $table->foreign('employee_id')->references('id')->on('employees');
            $table->foreign('employee_id_upfile')->references('id')->on('employees');
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
