<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableEmployeesAddTrialDate extends Migration
{
    private $table = 'employees';
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable($this->table) || Schema::hasColumn($this->table, 'trial_date')) {
            return;
        }
        Schema::table($this->table, function(Blueprint $table) {
            $table->date('trial_date')->after('join_date')->nullable();
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
