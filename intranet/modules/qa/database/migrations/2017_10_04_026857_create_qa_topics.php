<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Rikkei\QA\Model\Topic;

class CreateQATopics extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $table = Topic::getTableName();
        if (Schema::hasTable($table)) {
            return true;
        }
        Schema::create($table, function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->text('content')->nullable();
            $table->integer('qa_cate_id')->nullable();
            $table->integer('author_id')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->boolean('active')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $table = Topic::getTableName();
        Schema::drop($table);
    }
}
