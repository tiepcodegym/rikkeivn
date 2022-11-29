<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnTableOperationOverview extends Migration
{
    private $table = 'operation_overview';

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
            $table->string('offshore_vn')->nullable();
            $table->string('offshore_jp')->nullable();
            $table->string('offshore_en')->nullable();
            $table->string('onsite_jp')->nullable();
            $table->string('internal')->nullable();
            $table->string('other')->nullable();
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
            $table->dropColumn('offshore_vn');
            $table->dropColumn('offshore_jp');
            $table->dropColumn('offshore_en');
            $table->dropColumn('onsite_jp');
            $table->dropColumn('internal');
            $table->dropColumn('other');
        });
    }
}
