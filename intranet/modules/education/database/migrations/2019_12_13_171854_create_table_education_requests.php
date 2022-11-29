<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableEducationRequests extends Migration
{
    protected $table = 'education_requests';
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
            $table->unsignedInteger('employee_id');
            $table->unsignedInteger('type_id');
            $table->unsignedInteger('course_id')->nullable();
            $table->unsignedInteger('teacher_id')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('assign_id')->nullable();
            $table->tinyInteger('scope_total')->comment('1: company|2: branch| 3: division');
            $table->dateTime('start_date');
            $table->tinyInteger('status')->comment('1: closed|2: pending| 3: requesting| 4: opening| 5: reject');
            $table->timestamps();
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('type_id')->references('id')->on('education_types');
            $table->foreign('course_id')->references('id')->on('education_courses')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop($this->table);
    }
}
