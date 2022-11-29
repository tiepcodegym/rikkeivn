<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Rikkei\Notify\Classes\RkNotify;

class RemoveTableHomeMessageDateTimeOnly extends Migration
{
    protected $tbl = 'home_message_date_time_only';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table($this->tbl, function (Blueprint $table) {
            $table->dropIfExists();
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
