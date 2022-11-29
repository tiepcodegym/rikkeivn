<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCssTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('css', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('employee_id');
            $table->integer('project_type_id');
            $table->string('company_name');
            $table->string('customer_name');
            $table->string('project_name');
            $table->string('pm_name');
            $table->string('brse_name');
            $table->string('token');
            $table->date('start_date');
            $table->date('end_date');
            $table->timestamps();
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
        Schema::drop('css');
    }
}
