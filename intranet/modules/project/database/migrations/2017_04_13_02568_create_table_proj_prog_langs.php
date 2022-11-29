<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableProjProgLangs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('proj_prog_langs')) {
            return;
        }
        Schema::create('proj_prog_langs', function (Blueprint $table) {
            $table->unsignedInteger('project_id');
            $table->unsignedInteger('prog_lang_id');
            
            $table->primary(['project_id', 'prog_lang_id']);
            $table->foreign('project_id')
                ->references('id')->on('projs');
            $table->foreign('prog_lang_id')
                ->references('id')->on('programming_languages');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('proj_prog_langs');
    }
}
