<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableCustSurveyQuestions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('cust_survey_questions')) {
            return;
        }
        Schema::create('cust_survey_questions', function (Blueprint $table) {
            $table->increments('id');
            $table->text('question');
            $table->unsignedInteger('category_id');
            $table->integer('point');
            $table->boolean('required_flg')->default(0);
            $table->smallInteger('sort_order')->default(0);
            $table->smallInteger('project_type')->nullable();
            $table->dateTime('created_at');
            $table->unsignedInteger('created_by')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
            
            $table->index('category_id');
            $table->foreign('category_id')
                ->references('id')
                ->on('cust_survey_categories');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('cust_survey_questions');
    }
}
