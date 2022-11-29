<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableProjMembers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('project_members') || 
            !Schema::hasTable('projs')
        ) {
            return;
        }
        Schema::table('project_members', function (Blueprint $table) {
            $table->dropForeign('project_members_project_id_foreign');
            $table->unique(['project_id', 'employee_id']);
            $table->foreign('project_id')
                ->references('id')
                ->on('projs');
            
        });
        if (Schema::hasColumn('project_members', 'status')) {
            Schema::table('project_members', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }
        if (Schema::hasColumn('project_members', 'type')) {
            Schema::table('project_members', function (Blueprint $table) {
                $table->smallInteger('type')->change();
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
