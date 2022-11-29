<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableNotifyFlags extends Migration
{
    protected $tbl = 'notify_flags';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable($this->tbl)) {
            return;
        }
        Schema::create($this->tbl, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('employee_id');
            $table->foreign('employee_id')
                ->references('id')
                ->on('employees')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->tinyInteger('all_flg')->default(0); // nhận tất cả thông báo 1: off, 0: on
            $table->tinyInteger('admin_flg')->default(0); // nhận thông báo từ admin 1: off, 0: on
            $table->tinyInteger('period_flg')->default(0); // nhận thông báo định kỳ 1: off, 0: on
            $table->tinyInteger('timekeeping_flg')->default(0); // nhận thông báo về chấm công 1: off, 0: on
            $table->tinyInteger('project_flg')->default(0); // nhận thông báo từ dự án 1: off, 0: on
            $table->tinyInteger('resource_flg')->default(0); // nhận thông báo về nhân sự 1: off, 0: on
            $table->tinyInteger('other_flg')->default(0); // nhận thông báo khác 1: off, 0: on
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
        //
    }
}
