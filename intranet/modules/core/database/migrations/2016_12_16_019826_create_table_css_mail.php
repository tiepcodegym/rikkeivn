<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableCssMail extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('css_mail')) {
            return;
        }
        Schema::create('css_mail', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('css_id')->unique();
            $table->unsignedInteger('sender');
            $table->string('mail_to');
            $table->tinyInteger('made')->default(0);
            $table->tinyInteger('resend')->default(0);
            $table->dateTime('resend_date')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->foreign('css_id')
                ->references('id')
                ->on('css');
            $table->foreign('sender')
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
        Schema::drop('css_mail');
    }
}
