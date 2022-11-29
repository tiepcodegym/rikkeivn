<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLeaveDayReasonsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('leave_day_reasons')) {
            return;
        }
        
        Schema::create('leave_day_reasons', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->float('salary_rate');
            $table->integer('sort_order')->nullable();
            $table->tinyInteger('used_leave_day')->nullable()->default(0);
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('leave_day_reasons');
    }
}
