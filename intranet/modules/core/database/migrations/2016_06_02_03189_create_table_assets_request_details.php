<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableAssetsRequestDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('assets_request_details')) {
            return;
        }
        Schema::create('assets_request_details', function (Blueprint $table) {
            $table->unsignedInteger('request_id');
            $table->unsignedInteger('item_type_id');
            $table->unsignedInteger('amount');
            
            $table->primary(['request_id', 'item_type_id']);
            $table->index('item_type_id');
            $table->foreign('item_type_id')
                ->references('id')
                ->on('assets_types');
            $table->foreign('request_id')
                ->references('id')
                ->on('assets_requests');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('assets_request_details');
    }
}
