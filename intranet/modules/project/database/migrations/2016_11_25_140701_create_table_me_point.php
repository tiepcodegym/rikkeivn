<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableMePoint extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('me_points')) {
            Schema::create('me_points', function ($table) {
                $table->integer('eval_id')->unsigned();
                $table->integer('attr_id')->unsigned();
                $table->tinyInteger('point')->default(0);
                $table->primary(['eval_id', 'attr_id']);
                $table->foreign('eval_id')->references('id')->on('me_evaluations')->onDelete('cascade');
                $table->foreign('attr_id')->references('id')->on('me_attributes')->onDelete('cascade');
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
        Schema::dropIfExists('me_points');
    }
}
