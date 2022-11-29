<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterDateTableCandidates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('candidates', function ($table) {
            $table->dateTime('test_plan')->change();
            $table->dateTime('test_date')->change();
            $table->dateTime('interview_plan')->change();
            $table->dateTime('interview_date')->change();
            $table->dateTime('offer_date')->change();
            $table->dateTime('offer_feedback_date')->change();
            $table->dateTime('start_working_date')->change();
            $table->dateTime('trial_work_end_date')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('candidates', function ($table) {
            $table->date('test_plan')->change();
            $table->date('test_date')->change();
            $table->date('interview_plan')->change();
            $table->date('interview_date')->change();
            $table->date('offer_date')->change();
            $table->date('offer_feedback_date')->change();
            $table->date('start_working_date')->change();
            $table->date('trial_work_end_date')->change();
        });
    }
}
