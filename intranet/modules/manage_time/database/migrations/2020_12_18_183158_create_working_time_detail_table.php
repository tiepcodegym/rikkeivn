<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWorkingTimeDetailTable extends Migration
{
    protected $tbl = 'working_time_details';

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
            $table->unsignedInteger('working_time_id')->comment('nhan vien dang ky don');
            $table->unsignedInteger('employee_id')->comment('nhan vien duoc dang ky');
            $table->unsignedInteger('team_id')->nullable()->comment('Team hien tai cua nhan vien');
            $table->date('from_date');
            $table->date('to_date');
            $table->char('start_time1', 5);
            $table->char('end_time1', 5);
            $table->char('start_time2', 5);
            $table->char('end_time2', 5);
            $table->char('half_morning', 5);
            $table->char('half_afternoon', 5);
            $table->tinyInteger('key_working_time')->nullable();
            $table->tinyInteger('key_working_time_half')->nullable();
            $table->timestamps();
            $table->dateTime('deleted_at')->nullable();

            $table->foreign('working_time_id')->references('id')->on('working_time_registers')->onDelete('cascade');
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('team_id')->references('id')->on('teams')->onDelete('cascade');
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
