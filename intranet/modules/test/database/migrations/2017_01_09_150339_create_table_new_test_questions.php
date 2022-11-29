<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableNewTestQuestions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('ntest_questions')) {
            Schema::create('ntest_questions', function (Blueprint $table) {
               $table->increments('id');
               $table->text('content')->nullable();
               $table->text('image_urls')->nullable();
               $table->unsignedInteger('parent_id')->nullable();
               $table->timestamps();
               $table->foreign('parent_id')->references('id')->on('ntest_questions')->onDelete('set null');
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
        Schema::dropIfExists('ntest_questions');
    }
}
