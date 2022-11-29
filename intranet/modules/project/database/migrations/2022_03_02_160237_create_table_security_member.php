<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableSecurityMember extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('proj_security_member', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('employee_id');
            $table->unsignedInteger('security_id');
            $table->index('employee_id');
            $table->index('security_id');
            $table->foreign('employee_id')
                ->references('id')
                ->on('employees');
            $table->foreign('security_id')
                ->references('id')
                ->on('proj_op_security');
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
        Schema::drop('proj_security_member');
    }
}
