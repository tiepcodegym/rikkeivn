<?php

/**
 * @Author: nguyen kim trung
 * @Date:   2019-07-16 10:27:49
 * @Last Modified by:   nguyen kim trung
 * @Last Modified time: 2019-07-26 12:04:17
 */

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableManageFileGroupEmail extends Migration
{
    private $table = 'manage_file_group_email';

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
            $table->unsignedInteger('register_id');
            $table->string('group_email', 255);
            $table->foreign('register_id')->references('id')->on('manage_file_text');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop($this->table);
    }
}
