<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmplProjExperTags extends Migration
{
    protected $tbl = 'empl_proj_exper_tags';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable($this->tbl)) {
            return;
        }
        Schema::create($this->tbl, function (Blueprint $table) {
            $table->unsignedInteger('proj_exper_id');
            $table->unsignedInteger('tag_id')->nullable()
                ->comment('foreign kl_tag - field code');
            $table->string('tag_text')->nullable()
                ->comment('free tag text');
            $table->string('type', 10)->nullable()
                ->comment('field code, ex language');
            $table->string('lang', 3)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop($this->tbl);
    }
}
