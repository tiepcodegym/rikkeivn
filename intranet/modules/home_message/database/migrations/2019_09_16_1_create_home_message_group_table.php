<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHomeMessageGroupTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('home_message_groups')) {
            return;
        }

        Schema::create('home_message_groups', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 255);
            $table->tinyInteger('category')->comment('Mã phân loại nhóm');
            $table->tinyInteger('priority')->comment('Độ ưu tiên hiển thị');
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->unsignedBigInteger('created_id')->comment('User created');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('home_message_groups');
    }
}
