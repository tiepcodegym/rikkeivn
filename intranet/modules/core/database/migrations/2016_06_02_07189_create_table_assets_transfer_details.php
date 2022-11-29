<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableAssetsTransferDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('assets_transfer_details')) {
            return;
        }
        Schema::create('assets_transfer_details', function (Blueprint $table) {
            $table->unsignedInteger('transfer_id');
            $table->unsignedInteger('item_id');
            $table->unsignedInteger('amount');
            $table->integer('amount_before');
            $table->integer('amount_after');
            
            $table->primary(['transfer_id', 'item_id']);
            $table->index('item_id');
            $table->foreign('item_id')
                ->references('id')
                ->on('assets_items');
            $table->foreign('transfer_id')
                ->references('id')
                ->on('assets_transfers');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('assets_transfer_details');
    }
}
