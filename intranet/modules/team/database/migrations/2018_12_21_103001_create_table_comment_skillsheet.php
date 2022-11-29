<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableCommentSkillsheet extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('skillsheet_comments')) {
            return;
        }
        Schema::create('skillsheet_comments', function (Blueprint $table) {
            $table->increments('id');
            $table->text('content');
            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('employee_id');
            $table->foreign('employee_id')
                  ->references('id')
                  ->on('employees');
            $table->foreign('created_by')
                  ->references('id')
                  ->on('employees');
            $table->integer('type')->nullable();
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
        Schema::drop('skillsheet_comments');
    }
}
