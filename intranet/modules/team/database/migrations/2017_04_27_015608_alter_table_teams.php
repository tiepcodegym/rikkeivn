<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableTeams extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('teams')) {
            return;
        }
        if (Schema::hasColumn('teams', 'color')) {
            Schema::table('teams', function(Blueprint $table) {
                $table->dropColumn('color');
            });
        }
        if (!Schema::hasColumn('teams', 'code')) {
            Schema::table('teams', function(Blueprint $table) {
                $table->string('code', 20)->unique()->nullable();
                $table->index('code');
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
        
    }
}
