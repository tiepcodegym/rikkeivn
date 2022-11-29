<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableNewTestAnswers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('ntest_answers')) {
            if (!Schema::hasColumn('ntest_answers', 'is_temp')) {
                Schema::table('ntest_answers', function ($table) {
                    $table->boolean('is_temp');
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
        if (Schema::hasTable('ntest_answers')) {
            if (Schema::hasColumn('ntest_answers', 'is_temp')) {
                Schema::table('ntest_answers', function ($table) {
                    $table->dropColumn('is_temp');
                });
            }
        }
    }
}
