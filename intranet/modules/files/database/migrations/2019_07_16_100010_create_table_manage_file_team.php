<?php

/**
 * @Author: nguyen kim trung
 * @Date:   2019-07-16 10:27:49
 * @Last Modified by:   nguyen kim trung
 * @Last Modified time: 2019-07-26 12:23:16
 */

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableManageFileTeam extends Migration
{
    private $table = 'manage_file_team';
    
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
            $table->unsignedInteger('relater_id');
            $table->primary(['register_id', 'relater_id']);
            $table->foreign('register_id')->references('id')->on('manage_file_text');
            $table->foreign('relater_id')->references('id')->on('employees');
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
