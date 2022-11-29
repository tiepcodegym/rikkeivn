<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPmBrseJpToCssTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('css', function ($table) {
            $table->string('brse_name_jp');
            $table->string('pm_name_jp');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('css', function($table){
            $table->dropColumn(array('brse_name_jp', 'pm_name_jp'));
        });
    }
}
