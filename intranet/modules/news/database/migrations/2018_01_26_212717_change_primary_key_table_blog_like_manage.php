<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangePrimaryKeyTableBlogLikeManage extends Migration
{
    protected $tbl = 'blog_like_manage';
    protected $column = 'type';
    
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table($this->tbl, function ($table) {
            $table->dropForeign('blog_like_manage_employee_id_foreign');
            $table->dropForeign('blog_like_manage_post_id_foreign');
            $table->dropIndex('blog_like_manage_post_id_foreign');
            $table->dropPrimary();
            $table->primary(['employee_id', 'post_id', 'type']);
            $table->foreign('employee_id')
                ->references('id')->on('employees')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $table->dropForeign('blog_like_manage_employee_id_foreign');
        $table->dropForeign('blog_like_manage_post_id_foreign');
        $table->dropPrimary();
        $table->primary(['employee_id', 'post_id']);
        $table->foreign('employee_id')
            ->references('id')->on('employees')
            ->onDelete('cascade');
        $table->foreign('post_id')
            ->references('id')->on('blog_post_comments')
            ->onDelete('cascade');
    }
}
