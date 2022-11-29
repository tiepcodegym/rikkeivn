<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterProjectCalendarReportTable extends Migration
{
    protected $table = 'project_calendar_report';
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table($this->table, function(Blueprint $table) {
            $table->unsignedInteger('employee_id')->comment('khóa ngoại tới employees.id');
            $table->dropUnique('project_calendar_report_project_id_date_unique');
            $table->foreign('employee_id')->references('id')->on('employees');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table($this->table, function($table) {
            $table->dropForeign('project_calendar_report_employee_id_foreign');
            $table->dropColumn('employee_id');
        });
    }
}
