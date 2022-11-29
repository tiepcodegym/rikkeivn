<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterPriorityHomeMessageBannersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('home_message_banners', function (Blueprint $table) {
            if (Schema::hasColumn('home_message_banners', 'priority')) {
                $table->dropColumn('priority');
            }
            $table->dateTime('begin_at')->change();
            $table->dateTime('end_at')->change();
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
            if (!Schema::hasColumn('home_message_banners', 'priority')) {
                $table->integer('priority')->nullable();
            }
            $table->date('begin_at')->change();
            $table->date('end_at')->change();
        });
    }
}
