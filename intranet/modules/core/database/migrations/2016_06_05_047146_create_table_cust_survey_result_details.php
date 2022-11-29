<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableCustSurveyResultDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('cust_survey_result_details')) {
            return;
        }
        Schema::create('cust_survey_result_details', function (Blueprint $table) {
            $table->unsignedInteger('survey_result_id');
            $table->unsignedInteger('question_id');
            $table->smallInteger('point')->nullable();
            $table->text('comment');
            
            $table->primary(['survey_result_id', 'question_id']);
            $table->index('question_id');
            $table->foreign('question_id')
                ->references('id')
                ->on('cust_survey_questions');
            $table->foreign('survey_result_id')
                ->references('id')
                ->on('cust_survey_results');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('cust_survey_result_details');
    }
}
