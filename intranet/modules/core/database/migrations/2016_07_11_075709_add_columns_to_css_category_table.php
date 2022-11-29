<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsToCssCategoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('css_category', function ($table) {
            $table->boolean('show_brse_name');
            $table->boolean('show_pm_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('css_category', function($table){
            $table->dropColumn(array('show_brse_name', 'show_pm_name'));
        });
    }
}
