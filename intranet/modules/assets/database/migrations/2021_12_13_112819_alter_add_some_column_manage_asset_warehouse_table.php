<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAddSomeColumnManageAssetWarehouseTable extends Migration
{
    protected $tbl = 'manage_asset_warehouse';
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable($this->tbl)) {
            return;
        }
        Schema::table($this->tbl, function (Blueprint $table) {
            $table->unsignedInteger('manager_id')->unsigned()->nullable();
            $table->string('branch', 10)->nullable();

            $table->foreign('manager_id')->references('id')->on('employees')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (!Schema::hasTable($this->tbl)) {
            return;
        }
        Schema::table($this->tbl, function (Blueprint $table) {
            $table->dropForeign('manage_asset_warehouse_manager_id_foreign');

            $table->dropColumn('manager_id');
            $table->dropColumn('branch');
        });
    }
}
