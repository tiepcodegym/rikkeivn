<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTimekeepingLockHistories extends Migration
{
    private $tbl = 'timekeeping_lock_histories';

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
            $table->unsignedInteger('timekeeping_lock_id');
            $table->unsignedInteger('inform_id')->comment('id các đơn');
            $table->unsignedInteger('employee_id');
            $table->tinyInteger('type')->comment('2: công tác, 3: bổ sung công, 4: xin nghỉ phép, 5: bổ sung công OT');
            $table->tinyInteger('status')->comment('1: chưa cập nhật, 2 đã cập nhật');
            $table->timestamps();
            $table->dateTime('updated_status')->nullable();

            $table->foreign('timekeeping_lock_id')->references('id')->on('timekeeping_locks');
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
