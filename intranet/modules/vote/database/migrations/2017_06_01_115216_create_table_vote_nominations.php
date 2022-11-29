<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableVoteNominations extends Migration
{
    protected $tbl = 'vote_nominations';
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
           $table->increments('id');
           $table->unsignedInteger('vote_id');
           $table->unsignedInteger('nominee_id');
           $table->unsignedInteger('nominator_id')->nullable();
           $table->text('reason');
           $table->timestamps();
           $table->foreign('vote_id')->references('id')->on('votes')->onDelete('cascade');
           $table->foreign('nominee_id')->references('id')->on('employees')->onDelete('cascade');
           $table->foreign('nominator_id')->references('id')->on('employees')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists($this->tbl);
    }
}
