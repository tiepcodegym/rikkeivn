<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSynchronizeLogTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('synchronize_log', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('employee_id')->comment('Id người đồng bộ');
            $table->longText('employee_old')->comment('Thông tin nhân viên trước khi đồng bộ');
            $table->longText('employee_new')->comment('Thông tin nhân viên sau khi đồng bộ');
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
        Schema::drop('synchronize_log');
    }

}
