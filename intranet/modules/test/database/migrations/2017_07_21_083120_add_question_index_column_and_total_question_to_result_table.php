<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddQuestionIndexColumnAndTotalQuestionToResultTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ntest_results', function (Blueprint $table) {
            $table->mediumText('question_index')->nullable();
            $table->integer('total_question')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ntest_results', function (Blueprint $table) {
            $table->dropColumn('question_index');
            $table->dropColumn('total_question');
        });
    }
}
