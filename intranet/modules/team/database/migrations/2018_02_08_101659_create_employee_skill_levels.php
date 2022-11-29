<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmployeeSkillLevels extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('employee_skill_levels')) {
            return;
        }
        Schema::create('employee_skill_levels', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('employee_id');
            $table->unsignedInteger('tag_id')->nullable();
            $table->string('type')->nullable(); // same code in kl_fields
            $table->smallInteger('level')->nullable();
            $table->unsignedTinyInteger('exp_y')->nullable();
            $table->unsignedTinyInteger('exp_m')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();

            $table->index('employee_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('employee_skill_levels');
    }
}
