<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterEmployeeEducationsV1 extends Migration
{
    protected $tbl = 'employee_educations';
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
            if (!Schema::hasColumn($this->tbl, 'school_id')) {
                $table->unsignedInteger('school_id')->nullable();
            }
            if (!Schema::hasColumn($this->tbl, 'faculty_id')) {
                $table->unsignedInteger('faculty_id')->nullable();
            }
            if (!Schema::hasColumn($this->tbl, 'major_id')) {
                $table->unsignedInteger('major_id')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {}
}
