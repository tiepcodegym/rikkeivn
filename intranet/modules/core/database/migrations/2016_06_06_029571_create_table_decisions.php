<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableDecisions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('decisions')) {
            return;
        }
        Schema::create('decisions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code', 20);
            $table->string('title', 255);
            $table->text('content');
            $table->dateTime('published_at');
            $table->dateTime('start_at');
            $table->dateTime('end_at');
            $table->dateTime('created_at');
            $table->unsignedInteger('created_by')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('decisions');
    }
}
