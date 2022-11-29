<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmployeePolitic extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('employee_politic')) {
            return;
        }
        
        Schema::create('employee_politic', function (Blueprint $table) {
            $table->unsignedInteger('employee_id');
            $table->boolean('is_party_member')->nullable();
            $table->date('party_join_date')->nullable();
            $table->integer('party_position')->nullable();
            $table->string('party_join_place')->nullable();
            
            $table->boolean('is_union_member')->nullable();
            $table->date('union_join_date')->nullable();
            $table->integer('union_poisition')->nullable();
            $table->string('union_join_place')->nullable();
            
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
        Schema::drop('employee_politic');
    }
}
