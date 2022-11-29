<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableEmployeeCetificates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('employee_certificates')) {
            return;
        }
        Schema::create('employee_certificates', function (Blueprint $table) {
            $table->unsignedInteger('employee_id');
            $table->unsignedInteger('certificate_id');
            $table->smallInteger('level')->nullable();
            $table->date('start_at')->nullable();
            $table->date('end_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
            
            $table->primary(['employee_id', 'certificate_id']);
            $table->index('certificate_id');
            $table->foreign('certificate_id')
                ->references('id')
                ->on('certificates');
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
        Schema::drop('employee_certificates');
    }
}
