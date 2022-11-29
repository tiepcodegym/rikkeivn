<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmployeeCerties extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('employee_certies')) {
            return;
        }
        Schema::create('employee_certies', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('employee_id');
            $table->string('name')->nullable();
            $table->tinyInteger('type')->nullable();
            $table->string('level')->nullable();
            $table->date('start_at')->nullable();
            $table->date('end_at')->nullable();
            $table->string('place')->nullable();
            $table->float('p_listen')->nullable();
            $table->float('p_speak')->nullable();
            $table->float('p_read')->nullable();
            $table->float('p_write')->nullable();
            $table->string('p_sum', 100)->nullable();
            $table->text('note')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();

            $table->index('employee_id');
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('employee_certies');
    }
}
