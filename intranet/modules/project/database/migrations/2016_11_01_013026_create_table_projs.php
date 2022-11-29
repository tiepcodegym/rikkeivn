<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableProjs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('projs', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('cust_contact_id');
            $table->unsignedInteger('manager_id');
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->smallInteger('state')->nullable();
            $table->dateTime('start_at')->nullable();
            $table->dateTime('end_at')->nullable();
            $table->integer('created_by')->null();
            $table->dateTime('deleted_at')->nullable();
            $table->unsignedSmallInteger('type');
            $table->string('project_code', 255);
            $table->string('project_code_auto')->nullable();
            $table->index('cust_contact_id');
            $table->index('manager_id');
            $table->foreign('cust_contact_id')
                  ->references('id')
                  ->on('cust_contacts');
            $table->foreign('manager_id')
                  ->references('id')
                  ->on('employees');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('projs');
    }
}
