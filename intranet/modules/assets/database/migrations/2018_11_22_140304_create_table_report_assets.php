<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableReportAssets extends Migration
{
    protected $tbl = 'report_assets';
    protected $tblItem = 'report_asset_items';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable($this->tbl)) {
            Schema::create($this->tbl, function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('creator_id');
                $table->unsignedTinyInteger('type')->nullable();
                $table->unsignedTinyInteger('status')->default(1);
                $table->timestamps();
                $table->foreign('creator_id')
                        ->references('id')
                        ->on('employees')
                        ->onDelete('cascade');
            });
        }
        if (!Schema::hasTable($this->tblItem)) {
            Schema::create($this->tblItem, function (Blueprint $table) {
                $table->unsignedInteger('report_id');
                $table->unsignedInteger('asset_id');
                $table->unsignedTinyInteger('status')->default(1);
                $table->primary(['report_id', 'asset_id']);
                $table->foreign('report_id')
                        ->references('id')
                        ->on('report_assets')
                        ->onDelete('cascade');
                $table->foreign('asset_id')
                        ->references('id')
                        ->on('manage_asset_items')
                        ->onDelete('cascade');
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
        Schema::dropIfExists($this->tbl);
        Schema::dropIfExists($this->tblItem);
    }
}
