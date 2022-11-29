<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFinesMoneyTable extends Migration
{
    protected $table = 'fines_money';
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
                $table->string('amount', 10)->comment('số tiền phải nộp với type 1, chính là tổng của cột forgot_turn_off.amount group by month forgot_date và employee_id');
                $table->tinyInteger('count')->comment('Số lần vi phạm trong tháng');
                $table->tinyInteger('status_amount')->comment('Xác định đã nộp tiền phạt chưa 0: chưa nộp, 1: đã nộp');
                $table->tinyInteger('type')->comment('0: đi muộn, 1: quên tắt máy');
                $table->tinyInteger('month')->comment('Tháng phải nộp');
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
