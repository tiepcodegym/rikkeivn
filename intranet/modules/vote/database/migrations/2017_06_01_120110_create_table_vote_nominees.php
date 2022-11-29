<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableVoteNominees extends Migration
{
    protected $tbl = 'vote_nominees';
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
           $table->text('description')->nullable();
           $table->string('key', 64)->nullable();
           $table->boolean('confirm')->nullable();
           $table->unsignedInteger('created_by')->nullable();
           $table->softDeletes();
           $table->timestamps();
           $table->foreign('vote_id')->references('id')->on('votes')->onDelete('cascade');
           $table->foreign('nominee_id')->references('id')->on('employees')->onDelete('cascade');
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
