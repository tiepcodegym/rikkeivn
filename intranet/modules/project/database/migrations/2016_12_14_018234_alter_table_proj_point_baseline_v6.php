<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableProjPointBaselineV6 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('proj_point')) {
            if (!Schema::hasColumn('proj_point', 'css_css')) {
                Schema::table('proj_point', function (Blueprint $table) {
                    $table->float('css_css')->nullable();
                });
            }
            if (!Schema::hasColumn('proj_point', 'date_updated_css')) {
                Schema::table('proj_point', function (Blueprint $table) {
                    $table->dateTime('date_updated_css')->nullable();
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
