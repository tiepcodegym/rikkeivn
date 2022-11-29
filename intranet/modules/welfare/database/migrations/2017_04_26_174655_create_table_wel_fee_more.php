<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableWelFeeMore extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('wel_fee_more')) {
            return;
        }
        Schema::create('wel_fee_more', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('wel_id')->unsigned();
            $table->string('name')->unique();
            $table->string('source');
            $table->float('cost', 15, 2)->unsigned();
            $table->dateTime('created_at');
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->unsignedInteger('created_by')->nullable();

            $table->foreign('wel_id')->references('id')->on('welfares');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('wel_fee_more');
    }
}
