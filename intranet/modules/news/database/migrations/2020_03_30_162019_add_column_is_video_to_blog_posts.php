<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Rikkei\News\Model\Post;

class AddColumnIsVideoToBlogPosts extends Migration
{
    protected $tbl = 'blog_posts';
    protected $columnVideo = 'is_video';
    protected $columnVideoId = 'youtube_id';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable($this->tbl) || Schema::hasColumn($this->tbl, $this->columnVideo) || Schema::hasColumn($this->tbl, $this->columnVideoId)) {
            return;
        }
        Schema::table($this->tbl, function ($table) {
            $table->boolean($this->columnVideo)->default(0)->nullable();
            $table->string($this->columnVideoId)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table($this->tbl, function (Blueprint $table) {
            $table->dropColumn($this->columnVideo);
            $table->dropColumn($this->columnVideoId);
        });
    }
}
