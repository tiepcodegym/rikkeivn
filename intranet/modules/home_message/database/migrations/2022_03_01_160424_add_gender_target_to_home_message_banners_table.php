<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddGenderTargetToHomeMessageBannersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('home_message_banners', function (Blueprint $table) {
            //
            $table->tinyInteger('gender_target')
            ->default(null)->nullable()
            ->comment('Chọn giới tính áp dụng banner:
            0: nữ, 1: nam, Null: tất cả, default: Null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('home_message_banners', function (Blueprint $table) {
            //
            $table->dropColumn('gender_target');
        });
    }
}
