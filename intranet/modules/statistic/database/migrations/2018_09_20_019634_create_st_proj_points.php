<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStProjPoints extends Migration
{
    protected $tbl = 'st_proj_points';
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
            $table->smallInteger('type')->nullable();
            $table->string('value')->nullable();
            $table->unsignedInteger('team_id')->nullable();
            $table->text('detail')->nullable();
            $table->index('created_at');
            $table->index('type');
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
