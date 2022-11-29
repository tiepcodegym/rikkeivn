<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('users')) {
            if (!Schema::hasColumn('users', 'avatar_url')) {
                Schema::table('users', function ($table) {
                   $table->string('avatar_url')->nullable()->after('email'); 
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
        if (Schema::hasTable('users')) {
            if (Schema::hasColumn('users', 'avatar_url')) {
                Schema::table('users', function ($table) {
                   $table->dropColumn('avatar_url'); 
                });
            }
        }
    }
}
