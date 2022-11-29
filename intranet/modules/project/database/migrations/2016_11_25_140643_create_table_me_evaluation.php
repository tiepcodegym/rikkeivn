<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableMeEvaluation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('me_evaluations')) {
            Schema::create('me_evaluations', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('employee_id');
                $table->unsignedInteger('project_id');
                $table->timestamp('eval_time');
                $table->float('avg_point')->default('0');
                $table->tinyInteger('level_contribute')->default(1);
                $table->string('comment')->nullable();
                $table->unsignedInteger('manager_id');
                $table->timestamps();
                $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
                $table->foreign('project_id')->references('id')->on('projs')->onDelete('cascade');
                $table->foreign('manager_id')->references('id')->on('employees')->onDelete('cascade');
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
        Schema::dropIfExists('me_evaluations');
    }
}
