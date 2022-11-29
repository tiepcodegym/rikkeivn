<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFinesMoneyTable extends Migration
{
    private $table = 'fines_money';
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
            $table->string('amount', 20)->comment = 'số tiền phải nộp';
            $table->tinyInteger('status_amount')->default(0)->comment = '0:chưa nộp, 1 đã nộp';
            $table->tinyInteger('type')->comment = '0:đi muộn, 1 quên tắt máy';
            $table->tinyInteger('month')->comment = 'tháng phải nộp';
            $table->integer('year')->comment = 'năm phải nộp';
            $table->unsignedInteger('employee_id');
            $table->integer('count')->comment = 'số lần đi muộn';
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
