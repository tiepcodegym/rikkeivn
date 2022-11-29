<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddComlumnSuppportCostToTableWelRelativeAttachs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('wel_relative_attachs', function (Blueprint $table) {
            $table->integer('support_cost');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('wel_relative_attachs', function (Blueprint $table) {
            $table->dropColumn('support_cost');
        });
    }
}
