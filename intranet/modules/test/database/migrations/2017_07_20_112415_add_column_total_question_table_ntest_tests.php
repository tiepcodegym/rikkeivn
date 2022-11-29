<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnTotalQuestionTableNtestTests extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ntest_tests', function (Blueprint $table) {
            $table->integer('total_question');
            $table->boolean('limit_question');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ntest_tests', function (Blueprint $table) {
            $table->dropColumn('total_question');
            $table->dropColumn('limit_question');
        });
    }
}
