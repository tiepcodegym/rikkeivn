<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableWelAttachFee extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wel_attach_fee', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('wel_id');
            $table->integer('fee_free_count');
            $table->string('fee_free_relative');
            $table->integer('fee50_count');
            $table->string('fee50_relative');
            $table->integer('fee100_count')->nullable();
            $table->string('fee100_relative');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('wel_attach_fee');
    }
}
