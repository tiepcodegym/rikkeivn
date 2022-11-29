<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Rikkei\Project\Model\ProjectPoint;

class CreateTableProjPointFlat extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('proj_point_flat')) {
            return;
        }
        Schema::create('proj_point_flat', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('project_id');
            $table->smallInteger('summary')->default(ProjectPoint::COLOR_STATUS_BLUE);
            $table->smallInteger('cost')->default(ProjectPoint::COLOR_STATUS_BLUE);
            $table->smallInteger('quality')->default(ProjectPoint::COLOR_STATUS_BLUE);
            $table->smallInteger('tl')->default(ProjectPoint::COLOR_STATUS_BLUE);
            $table->smallInteger('proc')->default(ProjectPoint::COLOR_STATUS_BLUE);
            $table->smallInteger('css')->default(ProjectPoint::COLOR_STATUS_BLUE);
            $table->float('point_total')->default(0);
            
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            
            $table->index('project_id');
            $table->foreign('project_id')
                  ->references('id')
                  ->on('projs');
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('proj_point_flat');
    }
}
