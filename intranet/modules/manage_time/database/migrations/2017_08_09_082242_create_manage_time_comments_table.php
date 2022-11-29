<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateManageTimeCommentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('manage_time_comments')) {
            return;
        }

        Schema::create('manage_time_comments', function(Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('register_id');
            $table->text('comment');
            $table->tinyInteger('type');

            $table->unsignedInteger('created_by');
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('manage_time_comments');
    }
}
