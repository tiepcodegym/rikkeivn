<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateKlTagValues extends Migration
{
    
    protected $tbl = 'kl_tag_values';
    
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
            $table->unsignedInteger('field_id');
            $table->unsignedInteger('tag_id');
            $table->unsignedInteger('entity_id');
            
            $table->foreign('field_id')->references('id')->on('kl_fields');
            $table->foreign('tag_id')->references('id')->on('kl_tags');
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
