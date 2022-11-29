<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEducationTeachersTable extends Migration
{
    protected $tbl = 'education_teachers';
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
            $table->string('title');
            $table->string('scope');
            $table->integer('course_type_id')->unsigned();
            $table->string('type');
            $table->integer('course_id')->unsigned();
            $table->integer('class_id')->unsigned();
            $table->float('tranning_hour');
            $table->integer('tranning_manage_id')->unsigned()->nullable();
            $table->text('content')->nullable();
            $table->text('condition')->nullable();
            $table->integer('employee_id')->unsigned();
            $table->text('reject')->nullable();
            $table->string('status');
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
        Schema::dropIfExists($this->tbl);
    }
}
