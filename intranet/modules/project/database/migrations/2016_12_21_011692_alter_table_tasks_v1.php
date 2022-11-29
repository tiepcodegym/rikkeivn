<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableTasksV1 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('task_histories')) {
            if (!Schema::hasColumn('task_histories', 'created_by')) {
                Schema::table('task_histories', function (Blueprint $table) {
                    $table->integer('created_by')->nullable();
                });
            }
        }
        
        if (Schema::hasTable('task_comments')) {
            if (!Schema::hasColumn('task_comments', 'type')) {
                Schema::table('task_comments', function (Blueprint $table) {
                    $table->smallInteger('type')->nullable();
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
