<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableRequestAssetHistories extends Migration
{
    private $table = 'request_asset_histories';

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
            $table->unsignedInteger('request_id');
            $table->unsignedInteger('employee_id');
            $table->tinyInteger('action');
            $table->text('note')->nullable();
            $table->dateTime('created_at');
            $table->dateTime('updated_at');

            $table->foreign('request_id')->references('id')->on('request_assets');
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
