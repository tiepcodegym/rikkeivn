<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableProjectV1 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('projs')) {
            if (!Schema::hasColumn('projs','project_code_auto')) {
                Schema::table('projs', function (Blueprint $table) {
                    $table->string('project_code_auto')->nullable();
                });
            }
        }
        
        if (Schema::hasTable('source_server')) {
            if (!Schema::hasColumn('source_server','id_redmine_external')) {
                Schema::table('source_server', function (Blueprint $table) {
                    $table->string('id_redmine_external')->nullable();
                });
            }
            if (!Schema::hasColumn('source_server','id_git_external')) {
                Schema::table('source_server', function (Blueprint $table) {
                    $table->string('id_git_external')->nullable();
                });
            }
            if (!Schema::hasColumn('source_server','id_svn_external')) {
                Schema::table('source_server', function (Blueprint $table) {
                    $table->string('id_svn_external')->nullable();
                });
            }
            if (Schema::hasColumn('source_server','status')) {
                Schema::table('source_server', function (Blueprint $table) {
                    $table->string('status')->nullable()->change();
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
        //
    }
}
