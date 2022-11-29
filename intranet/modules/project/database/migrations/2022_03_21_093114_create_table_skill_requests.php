<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableSkillRequests extends Migration
{
    private $table = 'proj_op_skill_request';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable($this->table)) {
            return;
        }
        Schema::create($this->table, function (Blueprint $table) {
            $table->increments('id');
            $table->integer('proj_id');
            $table->text('skill');
            $table->text('category');
            $table->text('course_name');
            $table->text('mode');
            $table->text('provider');
            $table->text('required_for_role');
            $table->integer('hours');
            $table->text('level_assessment_method');
            $table->text('remark');
            $table->dateTime('deleted_at')->nullable();
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
        if (Schema::hasTable($this->table)) {
            Schema::dropIfExists($this->table);
        }
    }
}
