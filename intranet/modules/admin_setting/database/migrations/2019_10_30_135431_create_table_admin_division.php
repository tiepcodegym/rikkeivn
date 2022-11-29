<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableAdminDivision extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin_division', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->string('admin', 255)->comment = 'admin của division';
            $table->unsignedInteger('division')->comment = 'division';
            $table->integer('created_by');
            $table->integer('updated_by');
            $table->foreign('division')->references('id')->on('teams');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('admin_division');
    }
}
