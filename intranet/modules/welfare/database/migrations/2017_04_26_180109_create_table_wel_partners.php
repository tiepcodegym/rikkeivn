<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableWelPartners extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('wel_partners')) {
            return;
        }
        Schema::create('wel_partners', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('partner_id')->unsigned();
            $table->integer('wel_id')->unsigned();
            $table->tinyInteger('rep_gender');
            $table->string('rep_name', 50);
            $table->string('rep_position');
            $table->string('rep_phone', 15);
            $table->string('rep_phone_company', 15);
            $table->string('email', 50);
            $table->float('fee_return', 15, 2)->nullable();
            $table->text('note')->nullable();

            $table->foreign('wel_id')->references('id')->on('welfares');
            $table->foreign('partner_id')->references('id')->on('partners');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('wel_partners');
    }
}
