<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterManageTimeTimekeepingsV1 extends Migration
{
    private $table = 'manage_time_timekeepings';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable($this->table) || !Schema::hasColumn($this->table, 'id')) {
            return;
        }
        Schema::table($this->table, function(Blueprint $table) {
            $table->bigIncrements('id')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
