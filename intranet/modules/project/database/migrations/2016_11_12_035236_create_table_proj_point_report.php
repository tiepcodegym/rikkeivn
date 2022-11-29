<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableProjPointReport extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('proj_point_reports')) {
            return;
        }
        Schema::create('proj_point_reports', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('project_id');
            $table->tinyInteger('point')->default(0);
            $table->text('note')->nullable();
            $table->integer('changed_by')->unsigned()->nullable();
            $table->dateTime('changed_at')->nullable();
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
        Schema::drop('proj_point_reports');
    }
}
