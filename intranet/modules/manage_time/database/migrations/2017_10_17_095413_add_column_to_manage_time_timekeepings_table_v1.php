<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnToManageTimeTimekeepingsTableV1 extends Migration
{
    private $table = 'manage_time_timekeepings';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable($this->table) || Schema::hasColumn($this->table, 'ot_is_paid')) {
            return;
        }
        Schema::table($this->table, function(Blueprint $table) {
            $table->tinyInteger('ot_is_paid')->nullable()->default(0)->after('register_ot_number');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (!Schema::hasTable($this->table) || !Schema::hasColumn($this->table, 'ot_is_paid')) {
            return;
        }
        Schema::table($this->table, function (Blueprint $table) {
            $table->dropColumn('ot_is_paid'); 
        });
    }
}
