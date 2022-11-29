<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableMeAttributes3 extends Migration
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
                $table->boolean('can_fill')->default(1);
            });
            \Illuminate\Support\Facades\DB::table('me_attributes')->where('id', 1)->update(['can_fill' => 0]);
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
