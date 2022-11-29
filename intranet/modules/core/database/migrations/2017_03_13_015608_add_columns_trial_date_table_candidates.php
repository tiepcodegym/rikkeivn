<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsTrialDateTableCandidates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('candidates', function($table) {
            $table->date('start_working_date')->nullable();
            $table->date('trial_work_end_date')->nullable();
        });
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('candidates', function($table) {
            $table->dropColumn('start_working_date');
            $table->dropColumn('trial_work_end_date');
        });
    }
}
