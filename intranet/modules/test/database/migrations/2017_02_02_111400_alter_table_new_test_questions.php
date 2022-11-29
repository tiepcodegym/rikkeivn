<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableNewTestQuestions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('ntest_questions')) {
            if (!Schema::hasColumn('ntest_questions', 'type')) {
                Schema::table('ntest_questions', function ($table) {
                   $table->string('type', 10)->default('type1')->after('parent_id'); // type: 1, 2, 3, 4
                   $table->boolean('is_temp')->after('type');
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
        if (Schema::hasTable('ntest_questions')) {
            if (Schema::hasColumn('ntest_questions', 'type')) {
                Schema::table('ntest_questions', function ($table) {
                   $table->dropColumn('type');
                });
            }
            if (Schema::hasColumn('ntest_questions', 'is_temp')) {
                Schema::table('ntest_questions', function ($table) {
                   $table->dropColumn('is_temp'); 
                });
            }
        }
    }
}
