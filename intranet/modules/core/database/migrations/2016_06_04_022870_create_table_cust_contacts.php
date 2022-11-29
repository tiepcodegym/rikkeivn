<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableCustContacts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('cust_contacts')) {
            return;
        }
        Schema::create('cust_contacts', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('company_id');
            $table->string('title', 5)->nullable();
            $table->string('name', 255);
            $table->string('name_ja', 255)->nullable();
            $table->string('position', 255)->nullable();
            $table->string('image', 255);
            $table->dateTime('birthday')->nullable();
            $table->string('email', 100);
            $table->string('phone', 100)->nullable();
            $table->string('address', 255)->nullable();
            $table->string('skype', 45)->nullable();
            $table->string('chatwork', 45)->nullable();
            $table->string('facebook', 45)->nullable();
            $table->string('homepage', 255)->nullable();
            $table->text('note');
            $table->dateTime('created_at');
            $table->unsignedInteger('created_by')->nullable();
            $table->dateTime('updated_at')->nullable();
            
            $table->index('company_id');
            $table->foreign('company_id')
                ->references('id')
                ->on('cust_companies');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('cust_contacts');
    }
}
