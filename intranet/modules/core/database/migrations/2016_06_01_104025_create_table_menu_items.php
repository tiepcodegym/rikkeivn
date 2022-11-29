<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableMenuItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('menu_items')) {
            return;
        }
        Schema::create('menu_items', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('parent_id')->nullable();
            $table->unsignedInteger('menu_id');
            $table->unsignedInteger('action_id')->nullable();
            $table->string('name', 255);
            $table->smallInteger('state');
            $table->string('url')->nullable();
            $table->smallInteger('sort_order')->default(0);
            
            $table->index('menu_id');
            $table->index('action_id');
            $table->index('parent_id');
            $table->foreign('menu_id')
                ->references('id')
                ->on('menus');
            $table->foreign('action_id')
                ->references('id')
                ->on('actions');
            $table->foreign('parent_id')
                ->references('id')
                ->on('menu_items');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('menu_items');
    }
}
