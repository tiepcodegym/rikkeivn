<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMusicOfficesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('music_offices')) {
            return false;
        }
        Schema::create('music_offices', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name',255);
            $table->boolean('status')->default(0);
            $table->integer('sort_order')->nullable();
            $table->string('email_noti',50)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->integer('created_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('music_offices');
    }
}
