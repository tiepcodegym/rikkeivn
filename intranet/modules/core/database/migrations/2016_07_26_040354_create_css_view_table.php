<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCssViewTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('css_view', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('css_id');
            $table->string('name');
            $table->string('ip_address');
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
        Schema::drop('css_view');
    }
}
