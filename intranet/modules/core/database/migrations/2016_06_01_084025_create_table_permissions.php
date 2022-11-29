<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTablePermissions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('permissions')) {
            return;
        }
        Schema::create('permissions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('role_id');
            $table->unsignedInteger('team_id')->nullable();
            $table->unsignedInteger('action_id');
            $table->smallInteger('scope');
            $table->dateTime('created_at');
            $table->unsignedInteger('created_by')->nullable();
            $table->dateTime('deleted_at')->nullable();
            
            $table->index('role_id');
            $table->index('team_id');
            $table->index('action_id');
            $table->foreign('role_id')
                ->references('id')
                ->on('roles');
            $table->foreign('action_id')
                ->references('id')
                ->on('actions');
            $table->foreign('team_id')
                ->references('id')
                ->on('teams');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('permissions');
    }
}
