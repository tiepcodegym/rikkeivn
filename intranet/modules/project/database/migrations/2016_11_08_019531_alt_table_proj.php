<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AltTableProj extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('proj_op_criticals')) {
            if (Schema::hasColumn('proj_op_criticals', 'type')) {
                Schema::table('proj_op_criticals', function(Blueprint $table) {
                    $table->dropColumn('type');
                });
            }
        }
        if (Schema::hasTable('project_members')) {
            Schema::table('project_members', function (Blueprint $table) {
                $table->smallInteger('status');
                $table->unsignedInteger('parent_id')->nullable();
                $table->unsignedInteger('task_id')->nullable();
                
                $table->foreign('parent_id')
                    ->references('id')
                    ->on('project_members');
                $table->foreign('task_id')
                    ->references('id')
                    ->on('tasks');
            });
        }
        
        if (Schema::hasTable('proj_point')) {
            if (!Schema::hasColumn('proj_point', 'tl_deliver_note')) {
                Schema::table('proj_point', function (Blueprint $table) {
                    $table->text('tl_deliver_note')->nullable();
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
