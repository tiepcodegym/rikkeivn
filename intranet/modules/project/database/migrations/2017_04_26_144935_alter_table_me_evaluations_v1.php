<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableMeEvaluationsV1 extends Migration
{
    protected $tbl = 'me_evaluations';
    
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
            if (Schema::hasColumn($this->tbl, 'project_id')) {
                $table->unsignedInteger('project_id')->nullable()->change();
            }
            if (!Schema::hasColumn($this->tbl, 'team_id')) {
                if (Schema::hasColumn($this->tbl, 'project_id')) {
                    $table->unsignedInteger('team_id')->nullable()->after('project_id');
                } else {
                    $table->unsignedInteger('team_id')->nullable();
                }
                $table->foreign('team_id')->references('id')->on('teams')->onDelete('cascade');
            }
            if (!Schema::hasColumn($this->tbl, 'effort')) {
                $table->float('effort')->nullable()->after('proj_point');
            }
            if (!Schema::hasColumn($this->tbl, 'proj_index')) {
                $table->float('proj_index')->nullable()->after('effort');
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

    }
}
