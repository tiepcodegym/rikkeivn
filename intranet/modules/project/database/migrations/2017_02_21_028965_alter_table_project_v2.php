<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableProjectV2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('projs') && 
            Schema::hasColumn('projs', 'status_2')
        ) {
            Schema::table('projs', function (Blueprint $table) {
                  $table->renameColumn('status_2', 'status');
            });
        }
        if (Schema::hasTable('task_assigns') && 
            Schema::hasColumn('task_assigns', 'status_2')
        ) {
            Schema::table('task_assigns', function (Blueprint $table) {
                  $table->renameColumn('status_2', 'status');
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
