<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateForgotTurnOffTable extends Migration
{
    protected $table = 'forgot_turn_off';
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable($this->table)) {
            Schema::create($this->table, function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('employee_id')->comment('Id employee');
                $table->date('forgot_date')->comment('Ngày quên tắt máy tính');
                $table->string('amount', 10)->comment('Số tiền phải nộp');
                $table->string('ip_address', 50)->nullable()->comment('Ip máy');
                $table->string('computer_name', 255)->nullable()->comment('Tên máy tính');
                $table->string('area', 50)->nullable()->comment('Nơi để máy tính');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists($this->table);
    }
}
