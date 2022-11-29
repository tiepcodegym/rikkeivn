<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsToProjOpAssumptionsTable extends Migration
{
    protected $tableName = 'proj_op_assumptions';
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable($this->tableName)) {
            return;
        }
        if (Schema::hasColumn($this->tableName, 'impact')) {
            return;
        }
        if (Schema::hasColumn($this->tableName, 'action')) {
            return;
        }
        Schema::table($this->tableName, function (Blueprint $table) {
           $table->text('impact')->nullable();
           $table->text('action')->nullable(); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (!Schema::hasTable($this->tableName)) {
            return;
        }
        if (!Schema::hasColumn($this->tableName, 'impact')) {
            return;
        }
        if (Schema::hasColumn($this->tableName, 'action')) {
            return;
        }
        Schema::table($this->tableName, function (Blueprint $table) {
           $table->dropColumn('impact'); 
           $table->dropColumn('action'); 
        });
    }
}
