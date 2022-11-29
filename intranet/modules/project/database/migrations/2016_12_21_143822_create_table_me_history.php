<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableMeHistory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('me_histories')) {
            Schema::create('me_histories', function (Blueprint $table) {
               $table->bigIncrements('id');
               $table->unsignedInteger('eval_id');
               $table->unsignedInteger('employee_id');
               $table->integer('version')->default(1);
               $table->tinyInteger('action_type'); // 1: change point, 2: comment, 3: submit, 4: feedback, 5: accept, 6: close
               $table->unsignedInteger('type_id')->nullable();
               $table->text('content')->nullable();
               $table->timestamps();
               $table->foreign('eval_id')->references('id')->on('me_evaluations')->onDelete('cascade');
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
        Schema::dropIfExists('me_histories');
    }
}
