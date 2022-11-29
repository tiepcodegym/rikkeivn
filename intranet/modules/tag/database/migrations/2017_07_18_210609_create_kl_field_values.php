<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateKlFieldValues extends Migration
{
    
    protected $tbl = 'kl_field_values';
    
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
            $table->unsignedInteger('entity_id');
            $table->unsignedInteger('field_id');
            $table->unsignedInteger('item_id')->nullable();
            $table->text('value');
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('field_id')->references('id')->on('kl_fields');
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
