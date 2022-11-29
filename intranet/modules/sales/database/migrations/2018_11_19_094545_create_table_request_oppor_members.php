<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableRequestOpporMembers extends Migration
{
    protected $tbl = 'request_oppor_members';

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
            $table->bigIncrements('id');
            $table->unsignedInteger('request_oppor_id');
            $table->unsignedTinyInteger('number');
            $table->unsignedInteger('prog_id')->nullable();
            $table->unsignedTinyInteger('member_exp')->nullable();
            $table->unsignedTinyInteger('type')->default(1);
            $table->foreign('request_oppor_id')
                    ->references('id')
                    ->on('request_opportunities')
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
