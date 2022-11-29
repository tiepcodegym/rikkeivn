<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddColumnIsSoftDevTableTeamsFeature extends Migration
{
    protected $tbl = 'teams_feature';
    
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
        if (Schema::hasColumn($this->tbl, 'team_id')) {
            return;
        }
        Schema::table($this->tbl, function (Blueprint $table) {
           $table->tinyInteger('is_soft_dev')->nullable()->comment('Is software development'); 
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
           $table->dropColumn('is_soft_dev'); 
        });
    }
}
