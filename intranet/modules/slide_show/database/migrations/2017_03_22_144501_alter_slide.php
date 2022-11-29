<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterSlide extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('slide') || Schema::hasColumn('slide', 'font_size')) {
            return;
        }
        Schema::table('slide', function (Blueprint $table) {
            $table->integer('font_size')->nullable();
        });
    }
}
