<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableManageAssetItems extends Migration
{
    private $table = 'manage_asset_items';

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
            $table->increments('id');
            $table->string('code', 100);
            $table->string('name', 100);
            $table->unsignedInteger('team_id');
            $table->unsignedInteger('category_id');
            $table->unsignedInteger('supplier_id')->nullable();
            $table->unsignedInteger('origin_id')->nullable();
            $table->unsignedInteger('manager_id')->nullable();
            $table->string('serial', 100)->nullable();
            $table->text('specification')->nullable();
            $table->date('purchase_date')->nullable();
            $table->string('warranty_priod', 100)->nullable();
            $table->date('warranty_exp_date')->nullable();
            $table->unsignedInteger('employee_id')->nullable();
            $table->date('received_date')->nullable();
            $table->tinyInteger('state')->default(0);
            $table->date('change_date')->nullable();
            $table->text('reason')->nullable();
            $table->text('note')->nullable();
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
            $table->dateTime('deleted_at')->nullable();

            $table->index(['code', 'name']);
            $table->unique(['code']);
            $table->foreign('category_id')->references('id')->on('manage_asset_categories');
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
