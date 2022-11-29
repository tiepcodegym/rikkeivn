<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableProjPointBaselineV7 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('proj_point')) {
            if (!Schema::hasColumn('proj_point', 'report_last_at')) {
                Schema::table('proj_point', function (Blueprint $table) {
                    $table->dateTime('report_last_at')->nullable();
                });
            }
            if (!Schema::hasColumn('proj_point', 'raise')) {
                Schema::table('proj_point', function (Blueprint $table) {
                    $table->tinyInteger('raise')->default(2);
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
    }
}
