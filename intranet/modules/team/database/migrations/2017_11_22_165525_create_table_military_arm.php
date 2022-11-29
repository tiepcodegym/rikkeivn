<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableMilitaryArm extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasTable('military_arm')) {
            return;
        }
        Schema::create('military_arm', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 255);
            $table->boolean('state')->default(1)->comment("0 => Inactive, 1 => Active");
            
            $table->dateTime('created_at');
            $table->unsignedInteger('created_by')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable(); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('military_arm');
    }
}
