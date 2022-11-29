<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHomeMessageBannerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('home_message_banners')) {
            return;
        }

        Schema::create('home_message_banners', function (Blueprint $table) {
            $table->increments('id');
            $table->string('display_name', 255);
            $table->string('image', 255);
            $table->string('link', 255)->nullable();
            $table->date('begin_at')->comment('Thời gian bắt đầu hiện banner');
            $table->date('end_at')->comment('Thời gian dừng hiện banner');
            $table->integer('priority')->nullable()->comment('Độ ưu tiên hiển thị');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('home_message_banners');
    }
}
