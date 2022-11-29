<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Rikkei\News\Model\Post;

class AddColumnLinkToPosters extends Migration
{
    protected $tbl = 'posters';
    protected $columnLink = 'link';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable($this->tbl) || Schema::hasColumn($this->tbl, $this->columnLink)) {
            return;
        }
        Schema::table($this->tbl, function ($table) {
            $table->string($this->columnLink)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable($this->tbl) && Schema::hasColumn($this->tbl, $this->columnLink)) {
            Schema::table($this->tbl, function (Blueprint $table) {
                $table->dropColumn($this->columnLink);
            });
        }
    }
}
