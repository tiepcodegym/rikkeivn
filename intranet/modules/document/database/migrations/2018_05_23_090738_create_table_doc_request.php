<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableDocRequest extends Migration
{
    protected $tbl = 'doc_request';

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
            $table->unsignedInteger('doc_id');
            $table->unsignedInteger('request_id');
            $table->primary(['doc_id', 'request_id']);
            $table->foreign('doc_id')
                    ->references('id')
                    ->on('documents')
                    ->onDelete('cascade');
            $table->foreign('request_id')
                    ->references('id')
                    ->on('documentrequests')
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
