<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Rikkei\Notify\Classes\RkNotify;

class ChangeColumnTableHomeMessageDay extends Migration
{
    protected $tbl = 'home_message_day';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table($this->tbl, function (Blueprint $table) {
            $table->dropForeign('home_message_day_day_id_foreign');
            $table->dropColumn('day_id');
            $table->dropForeign('home_message_day_message_id_foreign');
            $table->renameColumn('message_id', 'home_message_id');
            $table->tinyInteger('type')->comment('1: Ngày cố định trong năm .2: Ngày trong tuần')->nullable();
            $table->string('permanent_day')->nullable();
            $table->tinyInteger('is_sun')->nullable()->comment('0: false .1: true');
            $table->tinyInteger('is_mon')->nullable()->comment('0: false .1: true');
            $table->tinyInteger('is_tues')->nullable()->comment('0: false .1: true');
            $table->tinyInteger('is_wed')->nullable()->comment('0: false .1: true');
            $table->tinyInteger('is_thur')->nullable()->comment('0: false .1: true');
            $table->tinyInteger('is_fri')->nullable()->comment('0: false .1: true');
            $table->tinyInteger('is_sar')->nullable()->comment('0: false .1: true');
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
