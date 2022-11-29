<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableEducationCourses extends Migration
{
    protected $tbl = 'education_courses';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable($this->tbl)) {
            return;
        }
        Schema::create($this->tbl, function (Blueprint $table) {
            $table->increments('id');
            $table->string('course_code');
            $table->text('name');
            $table->tinyInteger('status')->default(1);
            $table->tinyInteger('hours');
            $table->tinyInteger('type');
            $table->text('description');
            $table->text('target');
            $table->text('hr_feedback');
            $table->text('teacher_feedback');
            $table->string('education_cost');
            $table->string('teacher_cost');
            $table->tinyInteger('is_mail')->default(1);
            $table->timestamps();
            $table->integer('hr_id');
            $table->tinyInteger('scope_total');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists($this->tbl);
    }
}
