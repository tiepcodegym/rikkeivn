<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLocaleToDeviceTokensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('device_tokens') ||
            Schema::hasColumn('device_tokens', 'locale')) {
            return;
        }
        Schema::table('device_tokens', function (Blueprint $table) {
            $table->string('locale')->default('vi')->comment('thay đổi ngôn ngữ theo từng device');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('device_tokens', function (Blueprint $table) {
            $table->dropColumn('locale');
        });
    }
}
