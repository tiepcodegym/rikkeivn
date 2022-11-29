<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsTableCandidates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('candidates', function($table) {
            $table->unsignedInteger('presenter_id');
            $table->string('presenter_text', 100);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('candidates', function($table) {
            $table->dropColumn('presenter_id');
            $table->dropColumn('presenter_text');
        });
    }
}
