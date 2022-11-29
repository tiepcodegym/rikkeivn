<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableDocumentComment extends Migration
{
    protected $tbl = 'doc_comments';

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
            $table->unsignedInteger('doc_id');
            $table->text('content');
            $table->unsignedInteger('file_id')->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->tinyInteger('type')->default(1);
            $table->timestamps();
            $table->foreign('doc_id')
                    ->references('id')
                    ->on('documents')
                    ->onUpdate('cascade')
                    ->onDelete('cascade');
            $table->foreign('file_id')
                    ->references('id')
                    ->on('documentfiles')
                    ->onUpdate('cascade')
                    ->onDelete('set null');
            $table->foreign('created_by')
                    ->references('id')
                    ->on('employees')
                    ->onUpdate('cascade')
                    ->onDelete('set null');
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
