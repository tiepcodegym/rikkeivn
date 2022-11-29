<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableEmployees extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('employees')) {
            return;
        }
        Schema::create('employees', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('employee_card_id');
            $table->string('employee_code', 10);
            $table->string('name', 45);
            $table->string('japanese_name', 45)->nullable();
            $table->string('nickname', 20);
            $table->string('email', 100);
            $table->dateTime('join_date')->nullable();
            $table->dateTime('leave_date')->nullable();
            $table->string('personal_email', 100);
            $table->string('mobile_phone', 20);
            $table->string('home_phone', 20);
            $table->boolean('gender');
            $table->date('birthday')->nullable();
            $table->string('address', 150)->nullable();
            $table->string('home_town', 150)->nullable();
            $table->string('id_card_number')->nullable();
            $table->string('id_card_place')->nullable();
            $table->dateTime('id_card_date')->nullable();
            $table->unsignedInteger('recruitment_apply_id')->nullable();
            $table->smallInteger('state')->nullable();
            $table->dateTime('created_at');
            $table->unsignedInteger('created_by')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
            
            $table->unique('nickname');
            $table->unique('email');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('employees');
    }
}
