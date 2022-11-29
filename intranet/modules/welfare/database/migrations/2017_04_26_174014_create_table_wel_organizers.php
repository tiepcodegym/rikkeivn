<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableWelOrganizers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('wel_organizers')) {
            return;
        }
        Schema::create('wel_organizers', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('wel_id')->unsigned();
            $table->string('name', 50);
            $table->string('phone', 15);
            $table->string('position', 50);
            $table->string('phone_company', 15);
            $table->string('company', 45);
            $table->string('email_company', 50);
            $table->text('note');
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
        Schema::drop('wel_organizers');
    }
}
