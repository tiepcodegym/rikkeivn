<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableMeEvaluations2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('me_evaluations')) {
            \Illuminate\Support\Facades\DB::table('me_evaluations')
                    ->delete();
            Schema::table('me_evaluations', function ($table) {
                $table->datetime('eval_time')->default(null)->change();
            });
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
