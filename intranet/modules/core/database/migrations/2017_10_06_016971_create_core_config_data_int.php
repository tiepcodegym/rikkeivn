<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Rikkei\Core\Model\CoreConfigDataInt;

class CreateCoreConfigDataInt extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $table = CoreConfigDataInt::getTableName();
        if (Schema::hasTable($table)) {
            return true;
        }
        Schema::create($table, function (Blueprint $table) {
            $table->increments('id');
            $table->string('key');
            $table->integer('value')->nullable();
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
        $table = CoreConfigDataInt::getTableName();
        Schema::drop($table);
    }
}
