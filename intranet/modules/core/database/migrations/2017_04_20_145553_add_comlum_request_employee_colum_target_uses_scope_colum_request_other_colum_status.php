<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddComlumRequestEmployeeColumTargetUsesScopeColumRequestOtherColumStatus extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('assets_requests', function (Blueprint $table) {
            $table->integer('request_employee')->nullable();
            $table->string('scope_uses')->nullable();
            $table->string('request_other')->nullable();
            $table->integer('status')->nullable();
            $table->integer('team_request');
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
            $table->dropColumn('request_employee');
            $table->dropColumn('scope_uses');
            $table->dropColumn('request_other');
            $table->dropColumn('status');
            $table->dropColumn('team_request');
        });
    }
}
