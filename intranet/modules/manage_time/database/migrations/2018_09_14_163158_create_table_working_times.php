<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableWorkingTimes extends Migration
{
    protected $tbl = 'working_times';

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
            $table->unsignedInteger('employee_id');
            $table->unsignedInteger('approver_id')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->date('from_month');
            $table->date('to_month');
            $table->string('start_time1', 5);
            $table->string('end_time1', 5);
            $table->string('start_time2', 5);
            $table->string('end_time2', 5);
            $table->string('related_ids')->nullable();
            $table->text('reason');
            $table->timestamps();
            $table->foreign('parent_id')
                    ->references('id')
                    ->on($this->tbl)
                    ->onDelete('set null');
            $table->foreign('employee_id')
                    ->references('id')
                    ->on('employees')
                    ->onDelete('cascade');
            $table->foreign('approver_id')
                    ->references('id')
                    ->on('employees')
                    ->onDelete('set null');
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
