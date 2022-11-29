<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTypeToHomeMessageBannersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasColumn('home_message_banners', 'type')) {
            return;
        }
        Schema::table('home_message_banners', function (Blueprint $table) {
            $table->tinyInteger('type')->default(0);
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('home_message_banners', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
}
