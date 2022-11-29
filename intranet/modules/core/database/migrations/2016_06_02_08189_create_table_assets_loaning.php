<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableAssetsLoaning extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('assets_loaning')) {
            return;
        }
        Schema::create('assets_loaning', function (Blueprint $table) {
            $table->unsignedInteger('employee_id');
            $table->unsignedInteger('item_id');
            $table->smallInteger('amount');
            $table->dateTime('created_at');
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
            
            $table->primary(['employee_id', 'item_id']);
            $table->index('item_id');
            $table->foreign('employee_id')
                ->references('id')
                ->on('employees');
            $table->foreign('item_id')
                ->references('id')
                ->on('assets_items');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('assets_loaning');
    }
}
