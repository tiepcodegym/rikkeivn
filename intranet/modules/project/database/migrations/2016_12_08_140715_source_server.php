<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SourceServer extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('source_server', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('project_id');
            $table->string('id_redmine', 100)->nullable();
            $table->string('id_git', 100)->nullable();
            $table->string('id_svn', 100)->nullable();
            $table->string('id_redmine_external')->nullable();
            $table->string('id_git_external')->nullable();
            $table->string('id_svn_external')->nullable();
            $table->boolean('is_check_redmine')->default(0);
            $table->boolean('is_check_git')->default(0);
            $table->boolean('is_check_svn')->default(0);
            $table->unique(['id_redmine', 'status']);
            $table->unique(['id_git', 'status']);
            $table->unique(['id_svn', 'status']);
            $table->smallInteger('status')->nullable();
            $table->unsignedInteger('parent_id')->nullable();
            $table->unsignedInteger('task_id')->nullable();
            $table->integer('created_by')->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->index('parent_id');
            $table->index('project_id');
            $table->index('task_id');
            $table->foreign('parent_id')
                  ->references('id')
                  ->on('source_server');
            $table->foreign('project_id')
                  ->references('id')
                  ->on('projs');
            $table->foreign('task_id')
                  ->references('id')
                  ->on('tasks');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('source_server');
    }
}
