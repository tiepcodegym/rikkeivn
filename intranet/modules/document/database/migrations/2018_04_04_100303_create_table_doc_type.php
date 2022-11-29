<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableDocType extends Migration
{
    protected $tbl = 'doc_type';

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
            $table->unsignedInteger('type_id');
            $table->primary(['doc_id', 'type_id']);
            $table->foreign('doc_id')
                    ->references('id')
                    ->on('documents')
                    ->onUpdate('cascade')
                    ->onDelete('cascade');
            $table->foreign('type_id')
                    ->references('id')
                    ->on('documenttypes')
                    ->onUpdate('cascade')
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
