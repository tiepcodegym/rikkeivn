<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterManageTimeTimekeepingsTableV4 extends Migration
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
            if (!Schema::hasColumn($this->table, 'has_business_trip')) {
                $table->tinyInteger('has_business_trip')->nullable()->default(0)->after('early_end_shift');
            }
            if (!Schema::hasColumn($this->table, 'has_leave_day')) {
                $table->tinyInteger('has_leave_day')->nullable()->default(0)->after('register_business_trip_number');
            }
            if (!Schema::hasColumn($this->table, 'has_supplement')) {
                $table->tinyInteger('has_supplement')->nullable()->default(0)->after('register_leave_no_salary');
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
