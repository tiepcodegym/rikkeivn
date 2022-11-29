<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableMeEvaluations3 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('me_evaluations')) {
            if (Schema::hasColumn('me_evaluations', 'level_contribute')) {
                Schema::table('me_evaluations', function ($table) {
                    $table->dropColumn('level_contribute');
                });
            }
            if (!Schema::hasColumn('me_evaluations', 'version')) {
                Schema::table('me_evaluations', function ($table) {
                    $table->integer('version')->default(1)->after('avg_point');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
