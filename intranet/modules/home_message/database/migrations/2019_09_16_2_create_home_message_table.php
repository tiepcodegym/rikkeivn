<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHomeMessageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('home_messages')) {
            return;
        }

        Schema::create('home_messages', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('created_id')->comment('User created');
            $table->string('icon_url', 255);
            $table->string('message', 500);
            $table->dateTime('start_at');
            $table->dateTime('end_at');
            $table->tinyInteger('type')->comment('Loại message');
            $table->unsignedInteger('group_id');
            $table->tinyInteger('priority')->comment('Nếu Null lấy mặc định độ ưu tiên theo group đang trưc thuộc');
            $table->string('run_time', 5);
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->foreign('group_id')->references('id')->on('home_message_groups');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('home_messages');
    }
}
