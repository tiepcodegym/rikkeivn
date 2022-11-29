<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTableTimesheetItems extends Migration
{
    private $table = 'timesheet_items';

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
            $table->unsignedInteger('timesheet_id');
            $table->string('name')->comment('tên item');
            $table->string('roles')->nullable()->comment('Role item');
            $table->string('level')->nullable()->comment('Level Item');
            $table->date('working_from')->nullable();
            $table->date('working_to')->nullable();
            $table->string('line_item_id', 40)->commnet('ID item lưu bên Đơn hàng');
            $table->string('min_hour', 16)->nullable();
            $table->string('max_hour', 16)->nullable();
            $table->integer('division_id')->nullable();
            $table->integer('employee_id')->nullable();
            $table->float('day_of_leave')->nullable()->default(0);
            $table->timestamps();
            $table->foreign('timesheet_id')->references('id')->on('timesheets');
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
