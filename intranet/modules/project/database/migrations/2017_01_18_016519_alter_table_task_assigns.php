<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AlterTableTaskAssigns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('task_assigns')) {
            return;
        }
        if (!Schema::hasColumn('task_assigns', 'role')) {
            Schema::table('task_assigns', function (Blueprint $table) {
                $table->smallInteger('role');
            });
        }
        if (!Schema::hasColumn('task_assigns', 'status')) {
            Schema::table('task_assigns', function (Blueprint $table) {
                $table->smallInteger('status');
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
