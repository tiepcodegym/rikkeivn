<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableMeAttributes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('me_attributes')) {
            Schema::create('me_attributes', function (Blueprint $table) {
               $table->increments('id');
               $table->string('name');
               $table->tinyInteger('weight')->default(0);
               $table->integer('order')->default(0);
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('me_attributes');
    }
}
