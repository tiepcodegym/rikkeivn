<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableOtBreakTimes extends Migration
{
    private $table = 'ot_break_times';

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
            $table->unsignedInteger('ot_register_id');
            $table->unsignedInteger('employee_id');
            $table->date('ot_date')->nullable();
            $table->double('break_time', 8, 2)->nullable()->default(0);
            $table->dateTime('created_at');
            $table->dateTime('updated_at');

            $table->foreign('ot_register_id')->references('id')->on('ot_registers');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop($this->table);
    }
}
