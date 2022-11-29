<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTablePartners extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('partners')) {
            return;
        }
        Schema::create('partners', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code', 20);
            $table->string('name', 50);
            $table->integer('partner_type_id')->unsigned();
            $table->string('address')->nullable();
            $table->string('website')->nullable();
            $table->string('phone', 15)->nullable();
            $table->string('fax', 15)->nullable();
            $table->string('email', 50)->nullable();
            $table->string('tax_code', 50)->nullable();
            $table->string('bank_account', 50)->nullable();
            $table->string('bank_account_address')->nullable();
            $table->text('note')->nullable();
            $table->tinyInteger('rep_gender')->nullable();
            $table->string('rep_card_id', 20)->nullable();
            $table->dateTime('rep_card_id_date')->nullable();
            $table->string('rep_card_id_address')->nullable();
            $table->string('rep_name', 50)->nullable();
            $table->string('rep_position')->nullable();
            $table->string('rep_address')->nullable();
            $table->string('rep_phone_home', 15)->nullable();
            $table->string('rep_phone', 15)->nullable();
            $table->string('rep_phone_company', 15)->nullable();
            $table->string('rep_email', 50)->nullable();
            $table->dateTime('created_at');
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->unsignedInteger('created_by')->nullable();

            $table->foreign('partner_type_id')->references('id')->on('partner_types');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('partners');
    }
}
