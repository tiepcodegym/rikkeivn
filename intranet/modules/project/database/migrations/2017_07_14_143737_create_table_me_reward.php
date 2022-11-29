<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableMeReward extends Migration
{
    protected $tbl = 'me_reward';
    
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
            $table->unsignedInteger('eval_id');
            $table->double('reward_submit')->nullable();
            $table->text('comment')->nullable();
            $table->double('reward_approve')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
            $table->primary('eval_id');
            $table->foreign('eval_id')->references('id')->on('me_evaluations')
                    ->onDelete('cascade');
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
