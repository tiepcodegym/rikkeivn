<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateForgotTurnOffTable extends Migration
{
    private $table = 'forgot_turn_off';
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable($this->table)) {
            return;
        }
        Schema::create($this->table, function (Blueprint $table) {
            $table->increments('id');
            $table->dateTime('forgot_date')->comment('ngày quên tắt máy');
            $table->string('amount', 20)->comment = 'số tiền phải nộp';
            $table->string('ip_address', 50)->comment = 'ip máy';
            $table->string('computer_name', 255)->comment = 'tên máy';
            $table->string('area', 50)->comment = 'Nơi để máy tính';
            $table->unsignedInteger('employee_id');
            $table->foreign('employee_id')->references('id')->on('employees');
            $table->dateTime('updated_at');
            $table->dateTime('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop($this->table);
    }
}
