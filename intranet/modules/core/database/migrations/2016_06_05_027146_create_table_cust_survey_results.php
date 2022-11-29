<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableCustSurveyResults extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('cust_survey_results')) {
            return;
        }
        Schema::create('cust_survey_results', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('survey_id');
            $table->string('name', 255);
            $table->string('email', 255);
            $table->decimal('total_point', 10, 0);
            $table->string('comment_label');
            $table->text('comment');
            $table->dateTime('created_at');
            $table->dateTime('updated_at')->nullable();
            
            $table->index('survey_id');
            $table->foreign('survey_id')
                ->references('id')
                ->on('cust_survey');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('cust_survey_results');
    }
}
