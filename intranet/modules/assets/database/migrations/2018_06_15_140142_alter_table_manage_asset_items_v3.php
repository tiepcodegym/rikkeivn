<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableManageAssetItemsV3 extends Migration
{
    protected $table = 'manage_asset_items';
    protected $col = 'warehouse_id';
    protected $dropCol = 'address';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable($this->table) || Schema::hasColumn($this->table, $this->col)) {
            return;
        }
        Schema::table($this->table, function (Blueprint $table) {
            $table->unsignedInteger('warehouse_id');
            if (Schema::hasColumn($this->table, $this->dropCol)) {
                $table->dropColumn('address');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable($this->table) && Schema::hasColumn($this->table, $this->col)) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->dropColumn($this->col);
            });
        }
    }
}
