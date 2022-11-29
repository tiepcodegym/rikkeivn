<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropUniqueNameInTableWelFeeMore extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('wel_fee_more', function(Blueprint $table)
        {
            $table->dropUnique('wel_fee_more_name_unique');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('wel_fee_more', function(Blueprint $table)
        {
            $table->unique('name');
        });

    }
}
