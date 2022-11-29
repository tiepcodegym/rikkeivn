<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMultiChoiceColumnToTestQuestionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ntest_questions', function (Blueprint $table) {
            $table->boolean('multi_choice');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ntest_questions', function (Blueprint $table) {
            $table->dropColumn('multi_choice');
        });
    }
}
