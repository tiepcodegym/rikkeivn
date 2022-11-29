<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Rikkei\Notify\Classes\RkNotify;

class AddColumnStatusToHomeMessageBannersTable extends Migration
{
    protected $tbl = 'home_message_banners';
    protected $column = 'status';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable($this->tbl) || Schema::hasColumn($this->tbl, $this->column)) {
            return;
        }
        Schema::table($this->tbl, function (Blueprint $table) {
            $table->tinyInteger($this->column)->default(1);
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
