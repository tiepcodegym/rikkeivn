<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableOtDisallow extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ot_disallow', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->string('employee_id', 255)->comment = 'nhân viên thuộc division';
            $table->unsignedInteger('division')->comment = 'division';
            $table->integer('created_by');
            $table->integer('updated_by');
            $table->integer('deleted_by');
            $table->foreign('division')->references('id')->on('teams');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
}
