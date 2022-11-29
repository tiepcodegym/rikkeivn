<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableProjPointAddRaiseNoteColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('proj_point')) {

            Schema::table('proj_point', function($table){
                if (!Schema::hasColumn('proj_point', 'raise_note')) {
                    $table->text('raise_note')->nullable();
                }
            });
        }
        if (Schema::hasTable('proj_point_baselines')) {
            Schema::table('proj_point_baselines', function($table){
                if (!Schema::hasColumn('proj_point_baselines', 'raise_note')) {
                    $table->text('raise_note')->nullable();
                }
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
        Schema::table('proj_point', function($table){
            $table->dropColumn('raise_note');
        });
        Schema::table('proj_point_baselines', function($table){
            $table->dropColumn('raise_note');
        });
    }
}