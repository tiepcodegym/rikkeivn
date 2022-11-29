<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateConstractConfirmExpireTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contract_confirm_expire', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('contract_id')->comment('Mã loại hợp đồng');
            $table->tinyInteger('type')->comment('2: chưa xác nhận, 3: gia hạn, 4: chấm dứt');
            $table->string('note')->nullable();
            $table->timestamps();

            $table->foreign('contract_id')->references('id')->on('contracts');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('contract_confirm_expire');
    }

}
