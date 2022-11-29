<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateKlFields extends Migration
{
    
    protected $tbl = 'kl_fields';
    
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
            $table->string('code')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->tinyInteger('set')->default(1);
            $table->tinyInteger('type')->nullable();
            $table->unsignedInteger('parent_id')->nullable();
            $table->string('model')->nullable();
            $table->string('primary_column')->nullable();
            $table->string('column')->nullable();
            $table->string('color', 30)->nullable();
            $table->unsignedInteger('group_id')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->unsignedInteger('created_by')->nullable();
            
            $table->foreign('parent_id')->references('id')->on($this->tbl)
                    ->onDelete('set null')
                    ->onUpdate('cascade');
            $table->foreign('group_id')->references('id')->on('kl_field_groups')
                    ->onDelete('set null')
                    ->onUpdate('cascade');
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
