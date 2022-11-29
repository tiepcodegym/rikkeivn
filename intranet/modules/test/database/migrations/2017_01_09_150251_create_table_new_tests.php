<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableNewTests extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('ntest_tests')) {
            Schema::create('ntest_tests', function (Blueprint $table) {
               $table->increments('id');
               $table->string('url_code')->unique();
               $table->tinyInteger('type');
               $table->string('name');
               $table->string('slug');
               $table->tinyInteger('time');
               $table->boolean('is_auth');
               $table->boolean('random_order');
               $table->boolean('random_answer');
               $table->timestamps();
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
        Schema::dropIfExists('ntest_tests');
    }
}
