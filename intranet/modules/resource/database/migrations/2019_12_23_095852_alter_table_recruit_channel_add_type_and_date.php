<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableRecruitChannelAddTypeAndDate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('recruit_channel') || Schema::hasColumn('recruit_channel', 'type')) {
            return;
        }

        Schema::table('recruit_channel', function (Blueprint $table) {
            $table->tinyInteger('type')->default(0)->comment('Loại chi phí; 0: thay đổi; 1: cố định');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (!Schema::hasTable('recruit_channel') || !Schema::hasColumn('recruit_channel', 'type')) {
            return;
        }

        Schema::table('recruit_channel', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
}
