<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterProjectMetaV1 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('project_metas', function (Blueprint $table) {
            $table->text('env_dev')->nullable();
            $table->text('env_staging')->nullable();
            $table->text('env_production')->nullable();
            $table->text('others')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('project_memtas', function (Blueprint $table) {
            //
        });
    }
}
