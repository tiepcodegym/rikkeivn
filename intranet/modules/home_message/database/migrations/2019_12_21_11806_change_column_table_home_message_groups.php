<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Rikkei\Notify\Classes\RkNotify;

class ChangeColumnTableHomeMessageGroups extends Migration
{
    protected $tbl = 'home_message_groups';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table($this->tbl, function (Blueprint $table) {
            $table->renameColumn('name', 'name_vi');
            $table->integer('team_id')->nullable();
            $table->string('name_en')->nullable();
            $table->string('name_jp')->nullable();
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
