<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStProjLoc extends Migration
{
    protected $tbl = 'st_proj_loc';
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable($this->tbl)) {
            return true;
        }
        Schema::create($this->tbl, function (Blueprint $table) {
            $table->date('created_at')->nullable();
            $table->unsignedInteger('value')->nullable();
            $table->unsignedInteger('proj_id')->nullable();
            $table->string('team_id')->nullable(); // 4-1-14
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (!Schema::hasTable($this->tbl)) {
            return true;
        }
        Schema::drop($this->tbl);
    }
}
