<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterSliderV1 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('slide', function (Blueprint $table) {
            $table->tinyInteger('option')->default(0);
            $table->string('name_customer')->default(null);
            $table->tinyInteger('language')->default(0);
        });

    }
}
