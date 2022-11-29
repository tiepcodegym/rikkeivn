<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Rikkei\News\Model\Post;

class AddColumnImportantToBlogPosts extends Migration
{
    protected $tbl = 'blog_posts';
    protected $column = 'important';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable($this->tbl) || Schema::hasColumn($this->tbl, $this->column)) {
            return;
        }
        Schema::table($this->tbl, function ($table) {
            $table->boolean($this->column)->default(Post::STATUS_NOT_IMPORTANT);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable($this->tbl) && Schema::hasColumn($this->tbl, $this->column)) {
            Schema::table($this->tbl, function (Blueprint $table) {
                $table->dropColumn($this->column);
            });
        }
    }
}
