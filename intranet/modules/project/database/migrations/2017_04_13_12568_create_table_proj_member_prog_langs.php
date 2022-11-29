<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableProjMemberProgLangs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('proj_member_prog_langs')) {
            return;
        }
        Schema::create('proj_member_prog_langs', function (Blueprint $table) {
            $table->unsignedInteger('proj_member_id');
            $table->unsignedInteger('prog_lang_id');
            
            $table->primary(['proj_member_id', 'prog_lang_id']);
            $table->foreign('proj_member_id')
                ->references('id')->on('project_members');
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
