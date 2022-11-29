<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableManageAssetItemsV1 extends Migration
{
    private $table = 'manage_asset_items';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable($this->table) || Schema::hasColumn($this->table, 'request_id')) {
            return;
        }
        Schema::table($this->table, function(Blueprint $table) {
            $table->unsignedInteger('request_id')->nullable()->after('team_id');
            
            $table->foreign('request_id')->references('id')->on('request_assets');
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
