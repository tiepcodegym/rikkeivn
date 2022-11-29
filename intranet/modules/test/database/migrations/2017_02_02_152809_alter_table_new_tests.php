<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableNewTests extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('ntest_tests')) {
            if (!Schema::hasColumn('ntest_tests', 'description')) {
                Schema::table('ntest_tests', function ($table) {
                   $table->text('description')->after('time'); 
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
        if (Schema::hasTable('ntest_tests')) {
            if (Schema::hasColumn('ntest_tests', 'description')) {
                Schema::table('ntest_tests', function ($table) {
                   $table->dropColumn('description'); 
                });
            }
        }
    }
}
