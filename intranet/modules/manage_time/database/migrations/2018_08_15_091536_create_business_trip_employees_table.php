<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBusinessTripEmployeesTable extends Migration
{
    private $tbl = 'business_trip_employees';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable($this->tbl)) {
            return;
        }
        Schema::create($this->tbl, function (Blueprint $table) {
            $table->unsignedInteger('register_id');
            $table->unsignedInteger('employee_id');
            $table->dateTime('start_at');
            $table->dateTime('end_at');
            $table->text('note')->nullable();

            //Add foreign keys
            $table->foreign('register_id')->references('id')->on('business_trip_registers');
            $table->foreign('employee_id')->references('id')->on('employees');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop($this->tbl);
    }
}
