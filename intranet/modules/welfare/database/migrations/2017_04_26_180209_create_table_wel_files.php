<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableWelFiles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('wel_files')) {
            return;
        }
        Schema::create('wel_files', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('wel_id')->unsigned();
            $table->string('files')->unique();
            $table->dateTime('created_at');

            $table->foreign('wel_id')->references('id')->on('welfares');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('wel_files');
    }
}
