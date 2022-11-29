<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeColumnInTableWelFee extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('wel_fee', function (Blueprint $table) {
            $table->date('empl_offical_after_date')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('wel_fee', function (Blueprint $table) {
            $table->date('empl_offical_after_date')->change();
        });
    }
}
