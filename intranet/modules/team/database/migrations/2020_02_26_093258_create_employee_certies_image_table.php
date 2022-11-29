<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmployeeCertiesImageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employee_certies_image', function (Blueprint $table) {
            $table->increments('id');
            $table->string('image');
            $table->unsignedInteger('employee_certies_id');
            $table->timestamps();
            $table->foreign('employee_certies_id')->references('id')->on('employee_certies');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('employee_certies_image');
    }
}
