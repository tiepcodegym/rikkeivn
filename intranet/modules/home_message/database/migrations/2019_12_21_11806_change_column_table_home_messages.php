<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Rikkei\Notify\Classes\RkNotify;

class ChangeColumnTableHomeMessages extends Migration
{
    protected $tbl = 'home_messages';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table($this->tbl, function (Blueprint $table) {
            $table->renameColumn('message', 'message_vi');
            $table->removeColumn('run_time');
            $table->renameColumn('type', 'type_scheduler');
            $table->string('message_en')->nullable();
            $table->string('message_jp')->nullable();
            $table->integer('is_random')->nullable();
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
