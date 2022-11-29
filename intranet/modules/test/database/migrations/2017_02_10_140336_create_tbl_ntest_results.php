<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblNtestResults extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('ntest_results')) {
            Schema::create('ntest_results', function (Blueprint $table) {
               $table->increments('id');
               $table->unsignedInteger('employee_id')->nullable();
               $table->string('employee_email');
               $table->string('employee_name');
               $table->unsignedInteger('test_id');
               $table->integer('total_answers')->default(0);
               $table->integer('total_corrects')->default(0);
               $table->timestamp('leaved_at')->nullable();
               $table->timestamps();
               $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
               $table->foreign('test_id')->references('id')->on('ntest_tests')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ntest_results');
    }
}
