<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Rikkei\Notify\Classes\RkNotify;

class ChangeColumnTableNotifyFlags extends Migration
{
    protected $tbl = 'notify_flags';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table($this->tbl, function (Blueprint $table) {
            $table->smallInteger('all_flg')->default(RkNotify::ON_FLAG)->change(); // nhận tất cả thông báo 0: off, 1: on
            $table->smallInteger('admin_flg')->default(RkNotify::ON_FLAG)->change(); // nhận thông báo từ admin 0: off, 1: on
            $table->smallInteger('period_flg')->default(RkNotify::ON_FLAG)->change(); // nhận thông báo định kỳ 0: off, 1: on
            $table->smallInteger('timekeeping_flg')->default(RkNotify::ON_FLAG)->change(); // nhận thông báo về chấm công 0: off, 1: on
            $table->smallInteger('other_flg')->default(RkNotify::ON_FLAG)->change(); // nhận thông báo khác 0: off, 1: on
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
