<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableTestTempAddColumnAnswers extends Migration
{
    protected $tbl = 'ntest_test_temps';
    protected $col = 'str_answers';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable($this->tbl)) {
            return;
        }
        Schema::table($this->tbl, function (Blueprint $table) {
            if (!Schema::hasColumn($this->tbl, $this->col)) {
                $table->text($this->col)->nullable()->after('employee_email');
            }
            if (!Schema::hasColumn($this->tbl, 'employee_id')) {
                $table->unsignedInteger('employee_id')->nullable()->after($this->col);
            }
            if (!Schema::hasColumn($this->tbl, 'random_labels')) {
                $table->text('random_labels')->nullable()->after($this->col);
            }
            if (!Schema::hasColumn($this->tbl, 'question_index')) {
                $table->text('question_index')->nullable()->after($this->col);
            }
            if (!Schema::hasColumn($this->tbl, 'candidate_id')) {
                $table->unsignedInteger('candidate_id')->nullable()->after($this->col);
            }
            if (!Schema::hasColumn($this->tbl, 'employee_name')) {
                $table->string('employee_name')->nullable()->after($this->col);
            }
            if (!Schema::hasColumn($this->tbl, 'total_question')) {
                $table->unsignedInteger('total_question')->nullable()->after($this->col);
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
        //
    }
}
