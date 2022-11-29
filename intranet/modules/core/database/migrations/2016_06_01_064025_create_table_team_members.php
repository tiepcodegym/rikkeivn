<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableTeamMembers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('team_members')) {
            return;
        }
        Schema::create('team_members', function (Blueprint $table) {
            $table->unsignedInteger('team_id');
            $table->unsignedInteger('employee_id');
            $table->unsignedInteger('role_id');
            $table->dateTime('created_at');
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
            
            $table->primary(['team_id', 'employee_id', 'role_id']);
            $table->index('employee_id');
            $table->index('role_id');
            $table->foreign('employee_id')
                ->references('id')
                ->on('employees');
            $table->foreign('team_id')
                ->references('id')
                ->on('teams');
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
        Schema::drop('team_members');
    }
}
