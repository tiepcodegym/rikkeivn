<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblMagazineFiles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('magazine_files')) {
            Schema::create('magazine_files', function (Blueprint $table) {
                $table->increments('id');
                $table->string('title');
                $table->string('url');
                $table->string('type', 20)->default('image');
                $table->string('mimetype');
                $table->integer('employee_id')->unsigned()->nullable();
                $table->boolean('is_temp')->default(1);
                $table->timestamps();
                $table->foreign('employee_id')->references('id')->on('employees');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('magazine_files');
    }
}
