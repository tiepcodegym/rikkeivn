<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableWelRelativeAttachs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('wel_relative_attachs')) {
            return;
        }
        Schema::create('wel_relative_attachs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('welfare_id')->unsigned();
            $table->integer('employee_id')->unsigned();
            $table->string('name', 50);
            $table->integer('relation_name_id')->unsigned();
            $table->tinyInteger('gender');
            $table->string('card_id', 20)->nullable();
            $table->date('birthday')->nullable();
            $table->string('phone', 15)->nullable();
            $table->boolean('is_joined')->default(0);
            
            $table->dateTime('created_at');
            $table->dateTime('updated_at')->nullable();

            $table->foreign('welfare_id')->references('id')->on('welfares');
            $table->foreign('employee_id')->references('id')->on('employees');
            $table->foreign('relation_name_id')->references('id')->on('relation_names');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('wel_relative_attachs');
    }
}
