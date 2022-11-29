<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Rikkei\Project\Model\Project;

class AlterProjV1 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('projs', function (Blueprint $table) {
            $table->smallInteger('status')->default(Project::STATUS_APPROVED);
            $table->unsignedInteger('parent_id')->nullable();
            $table->unsignedInteger('task_id')->nullable();
            $table->foreign('parent_id')
                  ->references('id')
                  ->on('projs');
            $table->foreign('task_id')
                  ->references('id')
                  ->on('tasks');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('projs', function (Blueprint $table) {
            //
        });
    }
}
