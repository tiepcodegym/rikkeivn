<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Rikkei\News\Model\LikeManage;

class AddColumnTypeTableBlogLikeManage extends Migration
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
        if (!Schema::hasTable($this->tbl) || Schema::hasColumn($this->tbl, $this->column)) {
            return;
        }
        Schema::table($this->tbl, function ($table) {
            $table->tinyInteger($this->column)->default(LikeManage::TYPE_POST);
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
