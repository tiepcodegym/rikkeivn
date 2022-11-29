<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnFeeEstimatesInTableWelfareFee extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasColumn('wel_fee', 'fee_estimates')) {
            return;
        }
        Schema::table('wel_fee', function (Blueprint $table) {
            $table->float('fee_estimates', 15, 2);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('wel_fee', 'fee_estimates')) {
            Schema::table('wel_fee', function (Blueprint $table) {
                $table->dropColumn('fee_estimates');
            });
        }
    }
}
