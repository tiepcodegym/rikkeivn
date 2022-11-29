<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableTestResults extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('test_results')) {
            return;
        }
        Schema::create('test_results', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email', 100);
            $table->unsignedInteger('exam_id');
            $table->unsignedSmallInteger('point')->default(0);
            $table->unsignedSmallInteger('total_points');
            $table->smallInteger('state');
            $table->text('note');
            $table->dateTime('created_at');
            $table->dateTime('deleted_at')->nullable();
            
            $table->index('exam_id');
            $table->foreign('exam_id')
                ->references('id')
                ->on('test_exams');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('test_results');
    }
}
