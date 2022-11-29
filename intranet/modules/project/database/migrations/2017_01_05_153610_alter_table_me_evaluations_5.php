<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableMeEvaluations5 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('me_evaluations')) {
            if (!Schema::hasColumn('me_evaluations', 'is_leader_updated')) {
                Schema::table('me_evaluations', function ($table) {
                   $table->tinyInteger('is_leader_updated')->default(0)->after('last_user_updated'); 
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
