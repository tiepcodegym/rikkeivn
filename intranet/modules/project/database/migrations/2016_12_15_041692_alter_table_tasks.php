<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableTasks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tasks')) {
            return;
        }
        if (Schema::hasColumn('tasks', 'assign_first')) {
            Schema::table('tasks', function (Blueprint $table) {
                $table->dropForeign('tasks_assign_first_foreign');
                $table->dropColumn('assign_first');
            });
        }
        if (Schema::hasColumn('tasks', 'assign')) {
            Schema::table('tasks', function (Blueprint $table) {
                $table->dropForeign('tasks_assign_foreign');
                $table->dropColumn('assign');
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
