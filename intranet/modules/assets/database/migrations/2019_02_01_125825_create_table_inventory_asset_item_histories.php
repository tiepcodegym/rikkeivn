<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableInventoryAssetItemHistories extends Migration
{
    protected $tbl = 'inventory_asset_item_histories';
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable($this->tbl)) {
            return;
        }
        Schema::create($this->tbl, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('inventory_asset_item_id')->unsigned();
            $table->unsignedInteger('asset_id')->nullable();
            $table->string('asset_code');
            $table->string('asset_name');
            $table->tinyInteger('status');
            $table->text('note')->nullable();
            $table->timestamps();
            $table->foreign('inventory_asset_item_id')
                ->references('id')
                ->on('inventory_asset_items')
                ->onDelete('cascade');
            $table->foreign('asset_id')
                ->references('id')
                ->on('manage_asset_items')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop($this->tbl);
    }
}
