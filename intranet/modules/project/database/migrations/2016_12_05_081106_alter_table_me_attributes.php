<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableMeAttributes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('me_attributes')) {
            Schema::table('me_attributes', function ($table) {
               $table->string('label')->nullable()->after('name'); 
               $table->float('range_min')->after('order');
               $table->float('range_max')->after('range_min');
               $table->float('range_step')->after('range_max');
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
        //
    }
}
