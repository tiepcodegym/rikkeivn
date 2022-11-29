<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterManageTimeTimekeepingsTableV2 extends Migration
{
    private $table = 'manage_time_timekeepings';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table($this->table, function(Blueprint $table) {
            if (Schema::hasColumn($this->table, 'register_business_trip')) {
                $table->dropColumn('register_business_trip');
            }
            if (Schema::hasColumn($this->table, 'register_leave')) {
                $table->dropColumn('register_leave');
            }
            if (Schema::hasColumn($this->table, 'register_leave_number')) {
                $table->dropColumn('register_leave_number');
            }
            if (Schema::hasColumn($this->table, 'register_supplement')) {
                $table->dropColumn('register_supplement');
            }
            if (Schema::hasColumn($this->table, 'register_ot_number')) {
                $table->dropColumn('register_ot_number');
            }
            if (Schema::hasColumn($this->table, 'ot_is_paid')) {
                $table->dropColumn('ot_is_paid');
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
