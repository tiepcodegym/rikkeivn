<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterEmplProjExperTagsV1 extends Migration
{
    protected $tbl = 'empl_proj_exper_tags'; 

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable($this->tbl)) {
            return;
        }
        Schema::table($this->tbl, function (Blueprint $table) {
            if (Schema::hasColumn($this->tbl, 'tag_id')) {
                $table->unsignedInteger('tag_id')->nullable()
                    ->comment('foreign kl_tag - field code')->change();
            }
            if (Schema::hasColumn($this->tbl, 'type')) {
                $table->string('type', 10)->nullable()
                    ->comment('field code, ex language')->change();
            }
            if (!Schema::hasColumn($this->tbl, 'tag_text')) {
                $table->string('tag_text')->nullable()
                    ->comment('free tag text');
            }
            if (!Schema::hasColumn($this->tbl, 'lang')) {
                $table->string('lang', 3)->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
