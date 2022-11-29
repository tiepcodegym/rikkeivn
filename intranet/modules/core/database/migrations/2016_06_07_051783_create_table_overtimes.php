<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableOvertimes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('overtimes')) {
            return;
        }
        Schema::create('overtimes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('employee_id');
            $table->unsignedInteger('project_id');
            $table->unsignedInteger('team_id');
            $table->decimal('time', 10, 0);
            $table->date('date');
            $table->dateTime('start_at');
            $table->dateTime('end_at');
            $table->text('note');
            $table->smallInteger('state');
            $table->dateTime('created_at');
            $table->unsignedInteger('created_by')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
            
            $table->index('team_id');
            $table->index('employee_id');
            $table->index('project_id');
            $table->foreign('employee_id')
                ->references('id')
                ->on('employees');
            $table->foreign('project_id')
                ->references('id')
                ->on('projects');
            $table->foreign('team_id')
                ->references('id')
                ->on('teams');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('overtimes');
    }
}
