<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableMeComments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('me_comments')) {
            if (Schema::hasColumn('me_comments', 'attr_id')) {
                Schema::table('me_comments', function ($table) {
                   $table->unsignedInteger('attr_id')->nullable()->change(); 
                });
            }
            if (!Schema::hasColumn('me_comments', 'comment_type')) {
                Schema::table('me_comments', function ($table) {
                    $table->tinyInteger('comment_type')->default(1)->after('content'); //1. comment, 2.note
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
        if (Schema::hasTable('me_comments')) {
            if (Schema::hasColumn('me_comments', 'comment_type')) {
                Schema::table('me_comments', function ($table) {
                    $table->dropColumn('comment_type');
                });
            }
        }
    }
}
