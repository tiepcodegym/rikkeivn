<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMusicOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('music_orders')) {
            return false;
        }
        Schema::create('music_orders', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name',255);
            $table->string('link',255);
            $table->string('sender',255)->nullable();
            $table->string('receiver',255)->nullable();
            $table->text('message')->nullable();
            $table->boolean('is_play')->default(0);
            $table->integer('office_id')->unsigned();
            $table->foreign('office_id')->references('id')->on('music_offices')->onDelete('cascade');
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
        Schema::drop('music_orders');
    }
}
