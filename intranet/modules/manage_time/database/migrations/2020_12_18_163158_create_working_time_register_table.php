<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWorkingTimeRegisterTable extends Migration
{
    protected $tbl = 'working_time_registers';

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
            $table->unsignedInteger('parent_id')->nullable();
            $table->unsignedInteger('employee_id')->comment('nhan vien dang ky don');
            $table->unsignedInteger('approver_id')->comment('Nguoi duyet don');
            $table->unsignedInteger('updated_by')->nullable()->comment('Nhan vien update cuoi cung');
            $table->unsignedInteger('team_id')->nullable()->comment('Team hien tai cua nhan vien');
            $table->date('from_date')->nullable();
            $table->date('to_date')->nullable();
            $table->tinyInteger('key_working_time')->nullable();
            $table->tinyInteger('key_working_time_half')->nullable();
            $table->unsignedInteger('proj_id')->nullable();
            $table->tinyInteger('status')->default(1)->comment('1: chua duyet, 2: duyet, 3: tu choi');
            $table->string('related_ids')->nullable();
            $table->text('reason')->nullable();
            $table->timestamps();
            $table->dateTime('deleted_at')->nullable();

            $table->foreign('parent_id')->references('id')->on($this->tbl)->onDelete('set null');
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('approver_id')->references('id')->on('employees')->onDelete('cascade');
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
