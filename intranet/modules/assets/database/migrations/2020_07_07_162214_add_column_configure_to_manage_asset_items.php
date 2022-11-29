<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnConfigureToManageAssetItems extends Migration
{
    protected $tbl = 'manage_asset_items';
    protected $col = 'configure';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable($this->tbl) && !Schema::hasColumn($this->tbl, $this->col)) {
            Schema::table($this->tbl, function (Blueprint $table) {
                $table->text($this->col)->nullable()->comment('ghi thông tin cấu hình case - import file');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable($this->tbl) && Schema::hasColumn($this->tbl, $this->col)) {
            Schema::table($this->tbl, function (Blueprint $table) {
                $table->dropColumn($this->col);
            });
        }
    }
}
