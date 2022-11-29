<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableEmployeeRoles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('employee_roles')) {
            return;
        }
        Schema::create('employee_roles', function (Blueprint $table) {
            $table->unsignedInteger('employee_id');
            $table->unsignedInteger('role_id');
            $table->dateTime('start_at')->nullable();
            $table->dateTime('end_at')->nullable();
            $table->dateTime('created_at');
            $table->unsignedInteger('created_by')->nullable();
            $table->dateTime('updated_at')->nullable();
            
            $table->primary(['employee_id', 'role_id']);
            $table->index('role_id');
            $table->foreign('employee_id')
                ->references('id')
                ->on('employees');
            $table->foreign('role_id')
                ->references('id')
                ->on('roles');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('employee_roles');
    }
}
