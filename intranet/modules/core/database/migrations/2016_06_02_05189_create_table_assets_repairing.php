<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableAssetsRepairing extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('assets_repairing')) {
            return;
        }
        Schema::create('assets_repairing', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('item_id');
            $table->unsignedInteger('employee_id');
            $table->integer('amount');
            $table->smallInteger('type');
            $table->dateTime('start_at')->nullable();
            $table->dateTime('end_at')->nullable();
            $table->smallInteger('state');
            $table->dateTime('created_at');
            $table->unsignedInteger('created_by')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
            
            $table->index('item_id');
            $table->index('employee_id');
            $table->foreign('item_id')
                ->references('id')
                ->on('assets_items');
            $table->foreign('employee_id')
                ->references('id')
                ->on('employees');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('assets_repairing');
    }
}
