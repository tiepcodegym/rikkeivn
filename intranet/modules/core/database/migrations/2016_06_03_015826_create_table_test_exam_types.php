<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableTestExamTypes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('test_exam_types')) {
            return;
        }
        Schema::create('test_exam_types', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 100);
            $table->smallInteger('max_time');
            $table->smallInteger('total_questions');
            $table->dateTime('created_at');
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('test_exam_types');
    }
}
