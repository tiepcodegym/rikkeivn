<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHelpsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {   
        if (Schema::hasTable('helps')) {
            return;
        }
        Schema::create('helps', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->boolean('active')->nullable(false);
            $table->integer('parent')->unsigned()->nullable();           
            $table->integer('order')->nullable();
            $table->string('slug')->nullable();
            $table->text('content');
            $table->timestamps();
            $table->softDeletes();
            $table->integer('created_by')->nullable();
            
            $table->foreign('parent')->references('id')->on('helps');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('helps');
    }
}
