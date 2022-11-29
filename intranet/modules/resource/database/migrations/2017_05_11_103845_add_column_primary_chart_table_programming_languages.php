<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnPrimaryChartTableProgrammingLanguages extends Migration
{
    protected $tbl = 'programming_languages';
    
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
        if (Schema::hasColumn($this->tbl, 'primary_chart')) {
            return;
        }
        Schema::table($this->tbl, function (Blueprint $table) {
           $table->tinyInteger('primary_chart')->nullable()->comment('<> 1 group to others in chart'); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table($this->tbl, function (Blueprint $table) {
           $table->dropColumn('primary_chart'); 
        });
    }
}
