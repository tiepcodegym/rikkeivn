<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableVoteResults extends Migration
{
    
    protected $tbl = 'vote_results';
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
           $table->unsignedInteger('vote_nominee_id');
           $table->unsignedInteger('voter_id');
           $table->timestamps();
           $table->primary(['vote_nominee_id', 'voter_id']);
           $table->foreign('vote_nominee_id')->references('id')->on('vote_nominees')->onDelete('cascade');
           $table->foreign('voter_id')->references('id')->on('employees')->onDelete('cascade');
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
