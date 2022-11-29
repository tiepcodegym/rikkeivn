<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Rikkei\Notify\Classes\RkNotify;

class AddColumnNewsFlgToNotifyFlagsTable extends Migration
{
    protected $tbl = 'notify_flags';
    protected $column = 'news_flg';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable($this->tbl)) {
            return;
        }
        Schema::table($this->tbl, function (Blueprint $table) {
            if (!Schema::hasColumn($this->tbl, $this->column)) {
                $table->tinyInteger($this->column)->default(RkNotify::ON_FLAG);
            }
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
