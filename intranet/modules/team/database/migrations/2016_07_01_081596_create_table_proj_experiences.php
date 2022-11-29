<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableProjExperiences extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('proj_experiences')) {
            return;
        }
        Schema::create('proj_experiences', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('employee_id');
            $table->integer('work_experience_id')->nullable();
            $table->string('name');
            $table->date('start_at');
            $table->date('end_at');
            $table->text('enviroment');
            $table->text('responsible');
            $table->string('image')->nullable();
            $table->string('customer_name', 100)->nullable();
            $table->string('description', 255)->nullable();
            $table->integer('no_member')->nullable();
            $table->string('other_tech', 255)->nullable();
            $table->dateTime('created_at');
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->unsignedInteger('created_by')->nullable();

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
        Schema::drop('proj_experiences');
    }
}
