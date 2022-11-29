<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableManageAssetItemAttributes extends Migration
{
    private $table = 'manage_asset_item_attributes';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable($this->table)) {
            return;
        }
        Schema::create($this->table, function (Blueprint $table) {
            $table->unsignedInteger('asset_id');
            $table->unsignedInteger('attribute_id');
            $table->tinyInteger('state')->default(1);
            $table->dateTime('created_at');
            $table->dateTime('updated_at');

            $table->primary(['asset_id', 'attribute_id']);

            $table->foreign('asset_id')->references('id')->on('manage_asset_items');
            $table->foreign('attribute_id')->references('id')->on('manage_asset_attributes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop($this->table);
    }
}
