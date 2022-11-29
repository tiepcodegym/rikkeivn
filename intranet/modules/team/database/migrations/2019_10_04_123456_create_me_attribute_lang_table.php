<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateMeAttributeLangTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('me_attribute_lang', function (Blueprint $table) {
            $table->increments('id');
            $table->string('lang_code');
            $table->integer('attr_id');
            $table->string('name');
            $table->string('label')->nullable();
            $table->text('description')->nullable();
        });

        Schema::table('me_attributes', function (Blueprint $table) {
            $table->dropColumn('name');
            $table->dropColumn('label');
            $table->dropColumn('description');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('me_attribute_lang');

        Schema::table('me_attributes', function (Blueprint $table) {
            $table->string('name');
            $table->string('label')->nullable();
            $table->text('description')->nullable();
        });
    }
}
