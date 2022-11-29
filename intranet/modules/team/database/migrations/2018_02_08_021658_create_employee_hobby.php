<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmployeeHobby extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if( Schema::hasTable('employee_hobby') ) {
            return;
        }
        Schema::create('employee_hobby', function (Blueprint $table) {
            $table->unsignedInteger('employee_id');
            $table->text('personal_goal')->nullable();
            $table->text('hobby')->nullable();
            $table->text('forte')->nullable();
            $table->text('weakness')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->primary('employee_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('employee_hobby');
    }
}
