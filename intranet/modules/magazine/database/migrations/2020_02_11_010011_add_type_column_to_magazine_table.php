<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTypeColumnToMagazineTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasColumn('magazine', 'type')) {
            return;
        }
        Schema::table('magazine', function (Blueprint $table) {
            $table->tinyInteger('type')->default(1); //1: magazine, 2: document
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('magazine', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
}
