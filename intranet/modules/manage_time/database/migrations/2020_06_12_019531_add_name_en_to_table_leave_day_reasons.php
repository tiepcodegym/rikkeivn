<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNameEnToTableLeaveDayReasons extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('leave_day_reasons') ||
            Schema::hasColumn('leave_day_reasons', 'name_en')) {
            return;
        }
        Schema::table('leave_day_reasons', function (Blueprint $table) {
            $table->string('name_en')->nullable()->comment('Lưu tên tiếng anh');
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
            $table->drop('name_en');
        });
    }
}
