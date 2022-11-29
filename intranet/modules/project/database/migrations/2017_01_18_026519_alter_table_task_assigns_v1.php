<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Rikkei\Core\View\ModelHelper;
use Illuminate\Support\Facades\DB;

class AlterTableTaskAssignsV1 extends Migration
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
        if (ModelHelper::existsKey('task_assigns', 'PRIMARY', 'task_id') &&
            ModelHelper::existsKey('task_assigns', 'PRIMARY', 'employee_id')) {
            Schema::table('task_assigns', function (Blueprint $table) {
                DB::statement('ALTER TABLE task_assigns DROP PRIMARY KEY, '
                    . 'add PRIMARY KEY (`task_id`, `employee_id`, `role`)');
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
