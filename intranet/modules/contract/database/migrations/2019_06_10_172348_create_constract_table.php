<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateConstractTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contracts', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('employee_id');
            $table->foreign('employee_id')
                    ->references('id')
                    ->on('employees');
            $table->tinyInteger('type')->comment('Mã loại hợp đồng');
            $table->dateTime('start_at')->comment('Thời gian bắt đầu');
            $table->dateTime('end_at')->nullable()->comment('Thời gian kết thúc');
            $table->unsignedInteger('created_id')->comment('User created');
            $table->unsignedInteger('team_id')->comment('Phòng ban quản lý hồ sơ');
            $table->softDeletes();
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
        Schema::drop('contracts');
    }

}
