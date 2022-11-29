<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateRequestPriorityTable extends Migration
{
    
    protected $tbl = 'request_priority';
    
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
           $table->string('name_en');
           $table->string('name_jp');
           $table->boolean('state')->default(1);
           $table->dateTime('created_at');
           $table->dateTime('updated_at');
           $table->datetime('deleted_at')->nullable();
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
