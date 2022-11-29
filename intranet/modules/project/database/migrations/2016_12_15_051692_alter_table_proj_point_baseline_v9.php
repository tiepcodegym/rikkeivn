<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableProjPointBaselineV9 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('project_metas')) {
            $columnDelete = [
                'id_redmine',
                'id_git',
                'id_svn',
                'is_check_redmine',
                'is_check_git',
                'is_check_svn'
            ];
            foreach ($columnDelete as $item) {
                if (Schema::hasColumn('project_metas', $item)) {
                    Schema::table('project_metas', function (Blueprint $table) use ($item) {
                        $table->dropColumn($item);
                    });
                }
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
