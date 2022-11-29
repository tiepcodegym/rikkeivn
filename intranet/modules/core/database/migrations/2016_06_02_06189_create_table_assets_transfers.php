<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableAssetsTransfers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('assets_transfers')) {
            return;
        }
        Schema::create('assets_transfers', function (Blueprint $table) {
            $table->increments('id');
            $table->smallInteger('action');
            $table->unsignedInteger('from_employee_id')->nullable();
            $table->unsignedInteger('to_employee_id')->nullable();
            $table->text('note');
            $table->dateTime('created_at');
            $table->unsignedInteger('created_by')->nullable();
            $table->dateTime('deleted_at')->nullable();
            
            $table->index('from_employee_id');
            $table->index('to_employee_id');
            $table->foreign('from_employee_id')
                ->references('id')
                ->on('employees');
            $table->foreign('to_employee_id')
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
        Schema::drop('assets_transfers');
    }
}
