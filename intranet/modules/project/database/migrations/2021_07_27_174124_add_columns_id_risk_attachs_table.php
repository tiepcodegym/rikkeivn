<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsIdRiskAttachsTable extends Migration
{
    protected $tableName = 'risk_attachs';
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable($this->tableName)) {
            return;
        }
        Schema::table($this->tableName, function (Blueprint $table) {
            $table->increments('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (!Schema::hasTable($this->tableName)) {
            return;
        }
        Schema::table($this->tableName, function (Blueprint $table) {
           $table->dropColumn('id');
        });
    }
}
