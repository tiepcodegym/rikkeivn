<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCssResultTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('css_result', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('css_id');
            $table->string('name');
            $table->string('email');
            $table->string('proposed');
            $table->string('avg_point');
            $table->timestamps();
            $table->index('css_id');
            $table->foreign('css_id')
                ->references('id')
                ->on('css');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('css_result');
    }
}
