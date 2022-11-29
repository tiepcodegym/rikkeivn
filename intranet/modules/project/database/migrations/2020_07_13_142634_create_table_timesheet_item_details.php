<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTableTimesheetItemDetails extends Migration
{
    private $table = 'timesheet_item_details';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable($this->table)) {
            return;
        }

        Schema::create($this->table, function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('timesheet_item_id');
            $table->date('date');
            $table->string('checkin', 16)->nullable();
            $table->string('checkout', 16)->nullable();
            $table->float('working_hour')->nullable();
            $table->string('break_time', 16)->nullable()->comment('Thời gian nghỉ trưa');
            $table->float('ot_hour')->nullable();
            $table->float('overnight')->nullable();
            $table->tinyInteger('holiday')->default(0)->comment('0: k phải ngày lễ; 1: Ngày lễ');
            $table->string('note')->nullable()->comment('Ghi chú');
            $table->timestamps();
            // foreign key
            $table->foreign('timesheet_item_id')->references('id')->on('timesheet_items');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable($this->table)) {
            Schema::dropIfExists($this->table);
        }
    }
}
