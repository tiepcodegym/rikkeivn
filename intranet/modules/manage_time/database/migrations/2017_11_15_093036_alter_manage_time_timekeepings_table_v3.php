<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterManageTimeTimekeepingsTableV3 extends Migration
{
    private $table = 'manage_time_timekeepings';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable($this->table)) {
            return;
        }
        Schema::table($this->table, function(Blueprint $table) {
            if (!Schema::hasColumn($this->table, 'timekeeping_table_id')) {
                $table->unsignedInteger('timekeeping_table_id')->after('id');
                $table->foreign('timekeeping_table_id')->references('id')->on('manage_time_timekeeping_tables');
            }
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
