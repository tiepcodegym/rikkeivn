<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumCommentAndRequestOtherTableAssetsRequest extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('assets_requests', function (Blueprint $table) {
            $table->string('comment');
            $table->string('other_request');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('assets_requests', function (Blueprint $table) {
            $table->dropColumn('comment');
            $table->dropColumn('other_request');
        });
    }
}
