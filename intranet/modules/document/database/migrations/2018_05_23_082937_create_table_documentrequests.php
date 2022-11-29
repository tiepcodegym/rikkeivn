<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableDocumentrequests extends Migration
{
    protected $tbl = 'documentrequests';

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
            $table->text('content');
            $table->unsignedInteger('creator_id')->nullable();
            $table->text('note')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->unsignedInteger('created_by')->nullable();
            $table->timestamps();
            $table->foreign('created_by')
                    ->references('id')
                    ->on('employees')
                    ->onDelete('set null');
            $table->foreign('creator_id')
                    ->references('id')
                    ->on('employees')
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
