<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateManageTimeAttachmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('manage_time_attachments')) {
            return;
        }

        Schema::create('manage_time_attachments', function(Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('register_id');
            $table->string('file_name', 255);
            $table->string('path', 255);
            $table->string('mime_type')->nullable();
            $table->integer('size')->nullable();
            $table->tinyInteger('type');

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
        Schema::drop('manage_time_attachments');
    }
}
