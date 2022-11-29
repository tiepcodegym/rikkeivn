<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Rikkei\QA\Model\CateAllow;

class CreateQACateAllow extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $table = CateAllow::getTableName();
        if (Schema::hasTable($table)) {
            return true;
        }
        Schema::create($table, function (Blueprint $table) {
            $table->integer('qa_cate_id');
            $table->integer('employee_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $table = CateAllow::getTableName();
        Schema::drop($table);
    }
}
