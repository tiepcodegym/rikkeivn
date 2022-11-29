<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableMeAttributes4 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('me_attributes')) {
            if (!Schema::hasColumn('me_attributes', 'description')) {
                Schema::table('me_attributes', function ($table) {
                    $table->text('description')->nullable()->after('label');
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
