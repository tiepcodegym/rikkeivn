<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableManageAssetItemsV2 extends Migration
{
    private $table = 'manage_asset_items';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable($this->table) || Schema::hasColumn($this->table, 'allocation_confirm')) {
            return;
        }
        Schema::table($this->table, function(Blueprint $table) {
            $table->tinyInteger('allocation_confirm')->nullable()->after('note');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
