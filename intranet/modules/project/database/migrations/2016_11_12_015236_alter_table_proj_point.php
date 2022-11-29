<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Rikkei\Project\Model\ProjectPoint;

class AlterTableProjPoint extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('proj_point')) {
            Schema::table('proj_point', function (Blueprint $table) {
                $table->integer('task_id')->unsigned()->nullable();
                $table->integer('parent_id')->unsigned()->nullable();
                $table->smallInteger('status')->default(ProjectPoint::STATUS_APPROVED);
                $table->integer('changed_by')->unsigned()->nullable();
                
                $table->foreign('task_id')
                  ->references('id')
                  ->on('tasks');
                $table->foreign('parent_id')
                  ->references('id')
                  ->on('proj_point');
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
    }
}
