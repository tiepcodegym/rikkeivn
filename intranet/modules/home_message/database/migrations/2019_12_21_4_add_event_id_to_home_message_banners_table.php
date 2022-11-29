<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEventIdToHomeMessageBannersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasColumn('home_message_banners', 'event_id')) {
            return;
        }
        Schema::table('home_message_banners', function (Blueprint $table) {
            $table->tinyInteger('event_id')->nullable();
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
            $table->dropColumn('event_id');
        });
    }
}
