<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProjectMetas extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_metas', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('project_id');
            $table->decimal('size')->nullable();
            $table->decimal('price')->nullable();
            $table->decimal('incurred_cost')->nullable();
            $table->decimal('total_cost')->nullable();
            $table->string('source_url', 255)->nullable();
            $table->string('documents_url', 255)->nullable();
            $table->string('issues_url', 255)->nullable();
            $table->string('homepage_url', 255)->nullable();
            $table->string('schedule_link', 255)->nullable();
            $table->smallInteger('level')->nullable()->default(1);
            $table->string('lineofcode_baseline', 255)->nullable()->default(0);
            $table->string('lineofcode_current', 255)->nullable()->default(0);
            $table->text('scope_desc')->nullable();
            $table->text('scope_require')->nullable();
            $table->text('scope_customer_provide')->nullable();
            $table->text('scope_scope')->nullable();
            $table->text('scope_products')->nullable();
            $table->text('scope_env_test')->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->index('project_id');
            $table->foreign('project_id')
                  ->references('id')
                  ->on('projs');
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
        Schema::drop('project_metas');
    }
}
