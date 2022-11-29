<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableAssetsItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('assets_items')) {
            return;
        }
        Schema::create('assets_items', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('type_id');
            $table->string('code', 45);
            $table->string('name');
            $table->text('description');
            $table->unsignedInteger('amount');
            $table->dateTime('warranty')->nullable();
            $table->unsignedInteger('price');
            $table->unsignedInteger('avaiable_num');
            $table->float('depreciation')->nullable();
            $table->dateTime('created_at');
            $table->unsignedInteger('created_by')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
            
            $table->index('type_id');
            $table->foreign('type_id')
                ->references('id')
                ->on('assets_types');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('assets_items');
    }
}
