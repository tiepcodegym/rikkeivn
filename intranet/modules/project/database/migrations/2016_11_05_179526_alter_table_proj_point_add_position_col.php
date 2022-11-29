<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Rikkei\Project\Model\ProjectPoint;

class AlterTableProjPointAddPositionCol extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('proj_point')) {
            return;
        }
        Schema::table('proj_point', function (Blueprint $table) {
            if (!Schema::hasColumn('proj_point', 'position')) {
                $table->integer('position');
            }
            if (!Schema::hasColumn('proj_point', 'summary')) {
                $table->integer('summary')->default(ProjectPoint::COLOR_STATUS_BLUE);
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
