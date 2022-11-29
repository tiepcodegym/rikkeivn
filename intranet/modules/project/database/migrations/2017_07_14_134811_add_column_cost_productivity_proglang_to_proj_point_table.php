<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnCostProductivityProglangToProjPointTable extends Migration
{
    protected $tableName = 'proj_point';
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
        if (Schema::hasColumn($this->tableName, 'cost_productivity_proglang')) {
            return;
        }
        Schema::table($this->tableName, function (Blueprint $table) {
           $table->text('cost_productivity_proglang')->nullable()
                    ->comment('save productivity data at cost tab of project report');
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
        if (!Schema::hasColumn($this->tableName, 'cost_productivity_proglang')) {
            return;
        }
        Schema::table($this->tableName, function (Blueprint $table) {
           $table->dropColumn('cost_productivity_proglang'); 
        });
    }
}
