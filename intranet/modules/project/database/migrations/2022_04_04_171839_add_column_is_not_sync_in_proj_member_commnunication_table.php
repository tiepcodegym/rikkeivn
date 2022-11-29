<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnIsNotSyncInProjMemberCommnunicationTable extends Migration
{
    private $table='proj_member_communication';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable($this->table)) {
            return;
        }
        Schema::table($this->table, function (Blueprint $table) {
            $table->integer('is_not_sync')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (!Schema::hasTable($this->table)) {
            return;
        }
        Schema::table($this->table, function (Blueprint $table) {
            $table->dropColumn('is_not_sync');
        });
    }
}
