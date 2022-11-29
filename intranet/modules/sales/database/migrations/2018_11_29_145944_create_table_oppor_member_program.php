<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableOpporMemberProgram extends Migration
{
    protected $tbl = 'request_oppor_member_program';

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
            $table->unsignedBigInteger('req_member_id');
            $table->unsignedInteger('prog_id');
            $table->primary(['prog_id', 'req_member_id']);
            $table->foreign('req_member_id')
                    ->references('id')
                    ->on('request_oppor_members')
                    ->onDelete('cascade');
            $table->foreign('prog_id')
                    ->references('id')
                    ->on('programming_languages')
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
