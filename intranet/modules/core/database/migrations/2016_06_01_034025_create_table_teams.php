<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableTeams extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('teams')) {
            return;
        }
        Schema::create('teams', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 100);
            $table->smallInteger('type');
            $table->text('description');
            $table->unsignedInteger('leader_id')->nullable();
            $table->string('email', 100)->nullable();
            $table->unsignedInteger('parent_id')->nullable();
            $table->unsignedInteger('follow_team_id')->nullable();
            $table->boolean('is_function');
            $table->smallInteger('sort_order')->default(0);
            $table->dateTime('created_at');
            $table->unsignedInteger('created_by')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
            
            $table->index('leader_id');
            $table->foreign('leader_id')
                ->references('id')
                ->on('employees');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('teams');
    }
}
