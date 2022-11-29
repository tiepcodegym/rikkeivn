<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Rikkei\QA\Model\Comment;

class CreateQAComments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $table = Comment::getTableName();
        if (Schema::hasTable($table)) {
            return true;
        }
        Schema::create($table, function (Blueprint $table) {
            $table->integer('qa_topic_id');
            $table->integer('commenter_id')->nullable();
            $table->text('content')->nullable();
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
        $table = Comment::getTableName();
        Schema::drop($table);
    }
}
