<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTimekeepingLock extends Migration
{
    private $tbl = 'timekeeping_locks';

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
            $table->increments('id');
            $table->unsignedInteger('timekeeping_table_id');
            $table->dateTime('time_close_lock')->nullable();
            $table->dateTime('time_open_lock')->nullable();
            $table->timestamps();

            $table->foreign('timekeeping_table_id')->references('id')->on('manage_time_timekeeping_tables');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists($this->tbl);
    }
}
