<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableBlogPostCats extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('blog_post_cats')) {
            return false;
        }
        Schema::create('blog_post_cats', function (Blueprint $table) {
            $table->unsignedInteger('post_id');
            $table->unsignedInteger('cat_id');
            
            $table->primary(['post_id', 'cat_id']);
            $table->index('post_id');
            $table->index('cat_id');
            $table->foreign('post_id')
                  ->references('id')
                  ->on('blog_posts');
            $table->foreign('cat_id')
                  ->references('id')
                  ->on('blog_categories');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('blog_post_cats');
    }
}
