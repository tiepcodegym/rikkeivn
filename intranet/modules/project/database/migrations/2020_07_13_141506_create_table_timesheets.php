<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTableTimesheets extends Migration
{
    private $table = 'timesheets';

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
            $table->string('title');
            $table->unsignedInteger('project_id');
            $table->string('po_id', 40)->comment('ID đơn hàng lưu bên sale');
            $table->string('po_title')->nullable()->comment('tên item');
            $table->date('start_date')->nullable()->comment('Ngày bắt đầu timesheet');
            $table->date('end_date')->nullable()->comment('Ngày kết thúc timesheet');
            $table->integer('creator_id')->comment('Người tạo timesheet');
            $table->integer('updated_by')->comment('Người update timesheet');
            $table->string('start_overnight', 16)->nullable()->comment('thời gian bắt đầu tính overngiht');
            $table->string('end_overnight', 16)->nullable()->comment('thời gian kết thúc tính overngiht');

            $table->string('checkin_standard', 16)->nullable();
            $table->string('checkout_standard', 16)->nullable();
            $table->string('ot_normal_start', 16)->nullable();
            $table->string('ot_normal_end', 16)->nullable();
            $table->string('ot_day_off_start', 16)->nullable();
            $table->string('ot_day_off_end', 16)->nullable();
            $table->string('ot_holiday_start', 16)->nullable();
            $table->string('ot_holiday_end', 16)->nullable();
            $table->string('ot_overnight_start', 16)->nullable();
            $table->string('ot_overnight_end', 16)->nullable();

            $table->tinyInteger('status')->default(1)->comment('1: draft, 2: published');
            $table->timestamps();
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
