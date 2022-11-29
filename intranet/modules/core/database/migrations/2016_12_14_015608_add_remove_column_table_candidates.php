<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRemoveColumnTableCandidates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('candidates', function ($table) {
            $table->dropColumn(['test_email_date','interview_email_date']);
            $table->renameColumn('test_calling_date', 'test_plan');
            $table->renameColumn('interview_calling_date', 'interview_plan');
            $table->renameColumn('note', 'test_note');
            $table->text('interview_note')->nullable();
            $table->date('offer_date')->nullable();
            $table->string('offer_salary', 50)->nullable();
            $table->tinyInteger('offer_result')->default(0);
            $table->date('offer_feedback_date')->nullable();
            $table->text('offer_note')->nullable();
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
            $table->date('test_email_date');
            $table->date('interview_email_date');
            $table->renameColumn('test_plan', 'test_calling_date');
            $table->renameColumn('interview_plan', 'interview_calling_date');
            $table->renameColumn('test_note', 'note');
            $table->dropColumn('interview_note');
            $table->dropColumn('offer_date');
            $table->dropColumn('offer_salary');
            $table->dropColumn('offer_result');
            $table->dropColumn('offer_feedback_date');
            $table->dropColumn('offer_note');
        });
    }
}
