<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableManageAssetV3 extends Migration
{
    protected $table = 'manage_asset_items';

    protected $col = 'warehouse_id';
    
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable($this->table) || !Schema::hasColumn($this->table, $this->col)) {
            return;
        }
        Schema::table($this->table, function (Blueprint $table) {
            $table->unsignedInteger($this->col)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
