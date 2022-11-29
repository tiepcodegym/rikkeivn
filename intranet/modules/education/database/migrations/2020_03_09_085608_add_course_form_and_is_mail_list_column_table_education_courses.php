<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCourseFormAndIsMailListColumnTableEducationCourses extends Migration
{
    protected $table = 'education_courses';
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table($this->table, function (Blueprint $table) {
            $table->tinyInteger('course_form')->comment('1: truyen nghe | 2 khoa hoc')->default(1);
            $table->boolean('is_mail_list')->comment('0: false |1: true')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table($this->table, function (Blueprint $table) {
            $table->dropColumn('course_form');
            $table->dropColumn('is_mail_list');
        });
    }
}

