<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableEmployeesAddTrialEndDate extends Migration
{
    private $table = 'employees';
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable($this->table) || Schema::hasColumn($this->table, 'trial_end_date')) {
            return;
        }
        Schema::table($this->table, function(Blueprint $table) {
            $table->date('trial_end_date')->after('trial_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
