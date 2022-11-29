<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsFreequencyAndReportContentToTasksTable extends Migration
{
    protected $table = 'tasks';
    protected $columnFreequency = 'freequency_report';
    protected $columnReportContent = 'report_content';
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
        Schema::table($this->table, function (Blueprint $table) {
            if (!Schema::hasColumn($this->table, $this->columnFreequency)) {
                $table->integer($this->columnFreequency)->nullable()->after('content');
            }
            if (!Schema::hasColumn($this->table, $this->columnReportContent)) {
                $table->text($this->columnReportContent)->nullable()->after('content');
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
        Schema::table($this->table, function (Blueprint $table) {
            if (Schema::hasColumn($this->table, $this->columnFreequency)) {
                $table->dropColumn($this->columnFreequency);
            }
            if (Schema::hasColumn($this->table, $this->columnReportContent)) {
                $table->dropColumn($this->columnReportContent);
            }
        });
    }
}
