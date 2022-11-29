<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableEducationClass extends Migration
{
    protected $tbl = 'education_class';

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
            $table->string('class_code');
            $table->text('class_name');
            $table->integer('related_id');
            $table->string('related_name');
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->string('course_code');
            $table->unsignedInteger('course_id')->nullable();
            $table->timestamps();
            $table->tinyInteger('is_commitment')->default(1);
            $table->foreign('course_id')
                ->references('id')
                ->on('education_courses')
                ->onUpdate('cascade')
                ->onDelete('set null');
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
