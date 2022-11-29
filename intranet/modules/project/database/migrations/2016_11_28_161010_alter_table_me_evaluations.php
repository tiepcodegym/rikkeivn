<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableMeEvaluations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('me_evaluations')) {
            Schema::table('me_evaluations', function ($table) {
                $table->tinyInteger('status')->default(0)->after('manager_id'); // 0: draff, 1: new, 2: submited, 3: approved, 4: feedback, 5: closed
                $table->unsignedInteger('assignee')->nullable()->after('status');
                $table->foreign('assignee')->references('id')->on('employees')->onDelete('set null');
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
