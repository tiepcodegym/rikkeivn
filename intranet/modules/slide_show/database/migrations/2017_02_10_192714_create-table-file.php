<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableFile extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('file', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('slide_id');
            $table->string('file_name');
            $table->text('description');
            $table->tinyInteger('type_effect');
            $table->index('slide_id');
            $table->foreign('slide_id')
                ->references('id')
                ->on('slide');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('file');
    }
}
