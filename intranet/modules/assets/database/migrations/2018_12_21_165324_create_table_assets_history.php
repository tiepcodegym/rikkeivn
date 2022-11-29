<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableAssetsHistory extends Migration
{
    protected $tbl = 'assets_histories';
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
            $table->increments('id');
            $table->unsignedInteger('request_asset_history_id');
            $table->unsignedInteger('asset_id');
            $table->string('code', 100)->nullable();
            $table->string('name')->nullable();
            $table->tinyInteger('allocation_confirm')->nullable();
            $table->timestamps();
            $table->foreign('request_asset_history_id')
                    ->references('id')
                    ->on('request_asset_histories')
                    ->onDelete('cascade'); 
            $table->foreign('asset_id')
                    ->references('id')
                    ->on('manage_asset_items')
                    ->onDelete('cascade');
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
