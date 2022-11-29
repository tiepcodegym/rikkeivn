<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableManageAssetHistories extends Migration
{
    private $table = 'manage_asset_histories';

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
            $table->unsignedInteger('asset_id');
            $table->unsignedInteger('employee_id')->nullable();
            $table->text('note')->nullable();
            $table->tinyInteger('state');
            $table->date('change_date')->nullable();
            $table->text('change_reason')->nullable();
            $table->unsignedInteger('created_by');
            $table->dateTime('created_at');
            $table->dateTime('updated_at');

            $table->foreign('asset_id')->references('id')->on('manage_asset_items');
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
