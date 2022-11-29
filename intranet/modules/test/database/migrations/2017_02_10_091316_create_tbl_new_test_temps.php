<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblNewTestTemps extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('ntest_test_temps')) {
            Schema::create('ntest_test_temps', function (Blueprint $table) {
               $table->unsignedInteger('test_id');
               $table->string('employee_email');
               $table->timestamp('leaved_at')->nullable();
               $table->timestamps();
               $table->primary(['test_id', 'employee_email']);
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
        Schema::dropIfExists('ntest_test_temps');
    }
}
