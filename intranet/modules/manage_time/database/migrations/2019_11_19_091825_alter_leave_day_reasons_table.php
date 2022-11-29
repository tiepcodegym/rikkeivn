<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterLeaveDayReasonsTable extends Migration
{
    protected $table = 'leave_day_reasons';
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table($this->table, function(Blueprint $table) {
            $table->tinyInteger('team_type')->default(1)->comment('1: team Viet Nam, 2: team Japan')->after('sort_order');
            $table->tinyInteger('calculate_full_day')->default(0)->comment('0: Chỉ tính toán ngày làm việc, 1: Tính cả thứ 7 chủ nhật và ngày nghỉ lễ')->after('sort_order');
            $table->integer('value')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table($this->table, function (Blueprint $table) {
            $table->dropColumn('team_type');
            $table->dropColumn('calculate_full_day');
        });
    }
}
