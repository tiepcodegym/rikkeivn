<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Rikkei\Notify\Classes\RkNotify;

class ChangeColumnTableHomeMessagesV2 extends Migration
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
            $table->string('start_at')->change();
            $table->string('end_at')->change();
            $table->dropColumn('priority');
            $table->dropColumn('run_time');
            $table->dropColumn('type_scheduler');
            $table->dropColumn('is_random');
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
