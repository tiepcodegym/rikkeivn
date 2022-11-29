<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblMagazineImage extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        if (!Schema::hasTable('magazine_images')) {
            Schema::create('magazine_images', function (Blueprint $table) {
                $table->unsignedInteger('magazine_id');
                $table->unsignedInteger('image_id');
                $table->integer('order');
                $table->boolean('is_background');
                $table->primary(['magazine_id', 'image_id']);
                $table->foreign('magazine_id')->references('id')->on('magazine')->onDelete('cascade');
                $table->foreign('image_id')->references('id')->on('magazine_files')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('magazine_images');
    }

}
