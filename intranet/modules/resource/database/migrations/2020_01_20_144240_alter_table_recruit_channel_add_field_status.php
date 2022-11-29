<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableRecruitChannelAddFieldStatus extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('recruit_channel') || Schema::hasColumn('recruit_channel', 'status')) {
            return;
        }
        Schema::table('recruit_channel', function (Blueprint $table) {
            $table->tinyInteger('status')->default(1)->comment('1:active, 2:disable');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('recruit_channel', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
}

