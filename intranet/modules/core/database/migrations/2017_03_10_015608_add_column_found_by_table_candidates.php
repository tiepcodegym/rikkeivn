<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnFoundByTableCandidates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('candidates', function($table) {
            $table->unsignedInteger('found_by')->nullable();
        });
        Schema::table('candidates', function($table) {
            $table->foreign('found_by')->references('id')->on('employees');
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
            $table->dropForeign('candidates_found_by_foreign');
        });
        Schema::table('candidates', function($table) {
            $table->dropColumn('found_by');
        });
    }
}
