<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableMeComments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('me_comments')) {
            Schema::create('me_comments', function (Blueprint $table) {
               $table->increments('id');
               $table->unsignedInteger('eval_id');
               $table->unsignedInteger('attr_id');
               $table->unsignedInteger('employee_id');
               $table->string('employee_name');
               $table->tinyInteger('type')->default(1); // 1: GL, 2: PM, 3: ST
               $table->text('content');
               $table->timestamps();
               $table->foreign('eval_id')->references('id')->on('me_evaluations')->onDelete('cascade');
               $table->foreign('attr_id')->references('id')->on('me_attributes')->onDelete('cascade');
               $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
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
        Schema::dropIfExists('me_comments');
    }
}
