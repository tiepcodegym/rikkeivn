<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableMeEvaluations4 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('me_evaluations')) {
            if (!Schema::hasColumn('me_evaluations', 'last_user_updated')) {
                Schema::table('me_evaluations', function ($table) {
                   $table->unsignedInteger('last_user_updated')->nullable()->after('assignee'); 
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
