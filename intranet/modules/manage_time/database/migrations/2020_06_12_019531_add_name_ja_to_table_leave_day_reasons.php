<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNameJaToTableLeaveDayReasons extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('leave_day_reasons') ||
            Schema::hasColumn('leave_day_reasons', 'name_ja')) {
            return;
        }
        Schema::table('leave_day_reasons', function (Blueprint $table) {
            $table->string('name_ja')->nullable()->comment('Lưu tên tiếng nhật');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('leave_day_reasons', function (Blueprint $table) {
            $table->drop('name_ja');
        });
    }
}
