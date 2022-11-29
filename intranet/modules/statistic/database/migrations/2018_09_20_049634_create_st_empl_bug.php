<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStEmplBug extends Migration
{
    protected $tbl = 'st_empl_bug';
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
            $table->unsignedSmallInteger('type')->nullable(); // bug, leakage,..
            $table->unsignedInteger('empl_id')->nullable();
            $table->unsignedInteger('proj_id')->nullable();
            $table->string('team_id')->nullable(); // 4-1-14
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
