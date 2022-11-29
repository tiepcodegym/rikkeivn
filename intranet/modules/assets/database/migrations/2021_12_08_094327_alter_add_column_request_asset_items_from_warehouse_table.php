<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAddColumnRequestAssetItemsFromWarehouseTable extends Migration
{
    protected $tbl = 'request_asset_items_from_warehouse';
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
            $table->unsignedInteger('request_id')->unsigned()->after('employee_id');
            $table->integer('allocate')->default(0)->after('quantity');
            $table->integer('unallocate')->default(0)->after('allocate');

            $table->foreign('request_id')->references('id')->on('request_assets')->onDelete('cascade');
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
            $table->dropForeign('request_asset_items_from_warehouse_request_id_foreign');

            $table->dropColumn('request_id');
            $table->dropColumn('allocate');
            $table->dropColumn('unallocate');
        });
    }
}
