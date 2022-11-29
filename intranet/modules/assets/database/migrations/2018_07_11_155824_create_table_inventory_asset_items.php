<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableInventoryAssetItems extends Migration
{
    protected $tbl = 'inventory_asset_items';
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
            $table->unsignedInteger('inventory_id');
            $table->unsignedInteger('employee_id');
            $table->unsignedInteger('task_id')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->text('note')->nullable();
            $table->boolean('is_notify');
            $table->index(['inventory_id', 'employee_id']);
            $table->timestamps();
            $table->foreign('inventory_id')
                    ->references('id')
                    ->on('inventory_assets')
                    ->onDelete('cascade');
            $table->foreign('employee_id')
                    ->references('id')
                    ->on('employees')
                    ->onDelete('cascade');
            $table->foreign('task_id')
                    ->references('id')
                    ->on('tasks')
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
        //
    }
}
