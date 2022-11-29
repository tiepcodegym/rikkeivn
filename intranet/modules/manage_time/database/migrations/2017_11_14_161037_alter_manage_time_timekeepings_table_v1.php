<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterManageTimeTimekeepingsTableV1 extends Migration
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
            if (!Schema::hasColumn($this->table, 'register_leave_has_salary')) {
                $table->double('register_leave_has_salary', 8, 2)->nullable()->default(0)->after('register_business_trip_number');
            }
            if (!Schema::hasColumn($this->table, 'register_leave_no_salary')) {
                $table->double('register_leave_no_salary', 8, 2)->nullable()->default(0)->after('register_business_trip_number');
            }
            if (!Schema::hasColumn($this->table, 'register_ot_has_salary')) {
                $table->double('register_ot_has_salary', 8, 2)->nullable()->default(0)->after('register_ot');
            }
            if (!Schema::hasColumn($this->table, 'register_ot_no_salary')) {
                $table->double('register_ot_no_salary', 8, 2)->nullable()->default(0)->after('register_ot');
            }
            if (!Schema::hasColumn($this->table, 'is_official')) {
                $table->double('is_official', 8, 2)->nullable()->default(0)->after('timekeeping_number');
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
