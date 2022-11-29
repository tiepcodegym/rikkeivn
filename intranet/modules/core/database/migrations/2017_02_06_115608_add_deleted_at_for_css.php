<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDeletedAtForCss extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('css_team', function ($table) {
            $table->dateTime('deleted_at');
        });
        Schema::table('css_view', function ($table) {
            $table->dateTime('deleted_at');
        });
        Schema::table('css_result_detail', function ($table) {
            $table->dateTime('deleted_at');
        });
        Schema::table('css_result', function ($table) {
            $table->dateTime('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('css_team', function ($table) {
            $table->dropColumn('deleted_at');
        });
        Schema::table('css_view', function ($table) {
            $table->dropColumn('deleted_at');
        });
        Schema::table('css_result_detail', function ($table) {
            $table->dropColumn('deleted_at');
        });
        Schema::table('css_result', function ($table) {
            $table->dropColumn('deleted_at');
        });
    }
}
