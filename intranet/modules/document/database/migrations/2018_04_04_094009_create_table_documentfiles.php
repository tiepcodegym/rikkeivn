<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableDocumentfiles extends Migration
{
    protected $tbl = 'documentfiles';

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
            $table->string('name');
            $table->string('url');
            $table->longText('content')->nullable();
            $table->string('type', 16)->default('file');
            $table->string('mimetype');
            $table->unsignedInteger('author_id')->nullable();
            $table->timestamps();
            $table->foreign('author_id')
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
