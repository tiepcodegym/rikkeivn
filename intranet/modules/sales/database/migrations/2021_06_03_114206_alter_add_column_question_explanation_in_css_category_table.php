<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAddColumnQuestionExplanationInCssCategoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('css_category')) {
            return;
        }
        Schema::table('css_category', function (Blueprint $table) {
            $table->text('question_explanation')->nullable()->after('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('css_category', function (Blueprint $table) {
            $table->dropColumn('question_explanation');
        });
    }
}
