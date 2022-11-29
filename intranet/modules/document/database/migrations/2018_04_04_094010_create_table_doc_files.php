<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableDocFiles extends Migration
{
    protected $tbl = 'doc_file';

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
            $table->unsignedInteger('file_id');
            $table->string('version', 10)->default(1);
            $table->boolean('is_current');
            $table->tinyInteger('type')->default(1); //1. main file, 2. attach file
            $table->primary(['doc_id', 'file_id', 'version']);
            $table->foreign('doc_id')
                    ->references('id')
                    ->on('documents')
                    ->onUpdate('cascade')
                    ->onDelete('cascade');
            $table->foreign('file_id')
                    ->references('id')
                    ->on('documentfiles')
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
