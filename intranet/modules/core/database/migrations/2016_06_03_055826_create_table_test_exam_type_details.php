<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableTestExamTypeDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('test_exam_type_details')) {
            return;
        }
        Schema::create('test_exam_type_details', function (Blueprint $table) {
            $table->unsignedInteger('type_id');
            $table->unsignedInteger('category_id');
            $table->smallInteger('num');
            
            $table->primary(['type_id', 'category_id']);
            $table->index('category_id');
            $table->foreign('category_id')
                ->references('id')
                ->on('test_categories');
            $table->foreign('type_id')
                ->references('id')
                ->on('test_exam_types');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('test_exam_type_details');
    }
}
