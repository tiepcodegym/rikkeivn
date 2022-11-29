<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableBlogLikesManage extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('blog_like_manage')) {
            return;
        }
        Schema::create('blog_like_manage', function (Blueprint $table) {
            $table->integer('employee_id')->unsigned();
            $table->integer('post_id')->unsigned();
            $table->primary(['employee_id', 'post_id']);
            $table->foreign('employee_id')->references('id')->on('employees');
            $table->foreign('post_id')->references('id')->on('blog_posts');
//            $table->tinyInteger('like')->default(0);
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
        Schema::drop('blog_like_manage');
    }
}
