<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateTableProjsBusiness extends Migration
{
    private $table = 'projs_business';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable($this->table)) {
            return;
        }
        Schema::create($this->table, function (Blueprint $table) {
            $table->increments('id');
            $table->string('business_name');
            $table->tinyInteger('is_other_type');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable($this->table)) {
            Schema::dropIfExists($this->table);
        }
    }
}
