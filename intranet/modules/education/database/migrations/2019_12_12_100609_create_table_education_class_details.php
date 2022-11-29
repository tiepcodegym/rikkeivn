<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableEducationClassDetails extends Migration
{
    protected $tbl = 'education_class_details';

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
            $table->unsignedInteger('employee_id');
            $table->tinyInteger('role')->default(1);
            $table->integer('feedback_teacher_point');
            $table->integer('feedback_company_point');
            $table->text('feedback');
            $table->unsignedInteger('class_id')->nullable();
            $table->timestamps();
            $table->unsignedInteger('shift_id')->nullable();
            $table->tinyInteger('is_attend')->default(1);
            $table->tinyInteger('is_hr_added')->default(1);
            $table->tinyInteger('is_mail_sent')->default(0);
            $table->foreign('class_id')
                ->references('id')
                ->on('education_class')
                ->onUpdate('cascade')
                ->onDelete('set null');
            $table->foreign('shift_id')
                ->references('id')
                ->on('education_class_shifts')
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
