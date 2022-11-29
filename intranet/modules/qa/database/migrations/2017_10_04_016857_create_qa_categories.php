<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Rikkei\QA\Model\Category;

class CreateQACategories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $table = Category::getTableName();
        if (Schema::hasTable($table)) {
            return true;
        }
        Schema::create($table, function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->text('content')->nullable();
            $table->boolean('public')->default(1);
            $table->boolean('active')->default(1);
            $table->integer('author_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $table = Category::getTableName();
        Schema::drop($table);
    }
}
