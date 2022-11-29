<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeTypeColumnTableCandidates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('candidates', function ($table) {
            $table->unsignedInteger('request_id')->nullable()->change();
            $table->unsignedInteger('channel_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('candidates', function ($table) {
            $table->unsignedInteger('request_id')->change();
            $table->unsignedInteger('channel_id')->change();
        });
    }
}
