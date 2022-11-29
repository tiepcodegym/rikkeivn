<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFinesActionHistoryTable extends Migration
{
    private $table = 'fines_action_history';
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
            $table->integer('fines_money_id');
            $table->unsignedInteger('checker_id');
            $table->foreign('checker_id')->references('id')->on('employees');
            $table->unsignedInteger('object_fines');
            $table->foreign('object_fines')->references('id')->on('employees');
            $table->tinyInteger('month')->comment = 'tháng nộp';
            $table->integer('year')->comment = 'năm phải nộp';
            $table->string('amount', 20)->comment = 'số tiền phải nộp';
            $table->tinyInteger('action')->default(0)->comment = '0:chưa check, 1 đã check';
            $table->tinyInteger('type')->comment = '0:đi muộn, 1 quên tắt máy';
            $table->dateTime('checked_date')->comment = 'Thời điểm check';
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
        Schema::dropIfExists($this->table);
    }
}
