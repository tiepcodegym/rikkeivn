<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableVotes extends Migration
{
    protected $tbl = 'votes';
    
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
           $table->string('title');
           $table->string('slug');
           $table->text('content');
           $table->tinyInteger('status')->default(1);
           $table->dateTime('nominate_start_at')->nullable();
           $table->dateTime('nominate_end_at')->nullable();
           $table->dateTime('vote_start_at')->nullable();
           $table->dateTime('vote_end_at')->nullable();
           $table->integer('nominee_max')->nullable();
           $table->integer('vote_max')->nullable();
           $table->unsignedInteger('created_by')->nullable();
           $table->timestamps();
           $table->softDeletes();
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
