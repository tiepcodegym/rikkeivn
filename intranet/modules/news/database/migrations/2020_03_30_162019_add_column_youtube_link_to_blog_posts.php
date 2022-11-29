<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Rikkei\News\Model\Post;

class AddColumnYoutubeLinkToBlogPosts extends Migration
{
    protected $tbl = 'blog_posts';
    protected $columnVideo = 'youtube_link';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable($this->tbl) || Schema::hasColumn($this->tbl, $this->columnVideo)) {
            return;
        }
        Schema::table($this->tbl, function ($table) {
            $table->string($this->columnVideo)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable($this->tbl) && Schema::hasColumn($this->tbl, $this->columnVideo)) {
            Schema::table($this->tbl, function (Blueprint $table) {
                $table->dropColumn($this->columnVideo);
            });
        }
    }
}
