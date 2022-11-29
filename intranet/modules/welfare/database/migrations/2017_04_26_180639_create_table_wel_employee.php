<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableWelEmployee extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('wel_employee')) {
            return;
        }
        Schema::create('wel_employee', function (Blueprint $table) {
            $table->integer('wel_id')->unsigned();
            $table->integer('employee_id')->unsigned();
            $table->boolean('is_confirm')->default(0);
            $table->boolean('is_joined')->default(0);
            $table->dateTime('created_at');
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->unsignedInteger('created_by')->nullable();

            $table->foreign('wel_id')->references('id')->on('welfares');
            $table->foreign('employee_id')->references('id')->on('employees');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('wel_employee');
    }
}
