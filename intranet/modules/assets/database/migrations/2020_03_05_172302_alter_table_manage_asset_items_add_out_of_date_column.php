<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableManageAssetItemsAddOutOfDateColumn extends Migration
{
    protected $tbl = 'manage_asset_items';

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
            if (!Schema::hasColumn($this->tbl, 'out_of_date')) {
                $table->date('out_of_date')->nullable();
            }
            if (!Schema::hasColumn($this->tbl, 'days_before_alert_ood')) {
                $table->tinyInteger('days_before_alert_ood')->default(0);
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
        //
    }
}
