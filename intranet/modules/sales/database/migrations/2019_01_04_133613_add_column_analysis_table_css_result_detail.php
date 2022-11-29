<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnAnalysisTableCssResultDetail extends Migration
{
    protected $table = 'css_result_detail';
    protected $columnAnalysis = 'analysis';
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
            if (!Schema::hasColumn($this->table, $this->columnAnalysis)) {
                $table->text($this->columnAnalysis)->nullable()->after('comment');
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
        Schema::table('css_result_detail', function (Blueprint $table) {
            if (Schema::hasColumn($this->table, $this->columnAnalysis)) {
                $table->dropColumn($this->columnAnalysis);
            }
        });
    }
}
