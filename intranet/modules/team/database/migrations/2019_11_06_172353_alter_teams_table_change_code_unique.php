<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTeamsTableChangeCodeUnique extends Migration
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
        $schemaMan = Schema::getConnection()->getDoctrineSchemaManager();
        $tblIndexes = $schemaMan->listTableIndexes('teams');
        if (!array_key_exists('teams_code_unique', $tblIndexes)) {
            return;
        }
        Schema::table('teams', function (Blueprint $table) {
            $table->dropUnique('teams_code_unique');
            $table->dropIndex('teams_code_index');
            $table->unique(['deleted_at', 'code']);
        });
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
