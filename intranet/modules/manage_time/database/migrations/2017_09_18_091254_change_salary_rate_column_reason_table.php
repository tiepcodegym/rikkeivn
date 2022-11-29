<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeSalaryRateColumnReasonTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('leave_day_reasons', function (Blueprint $table) {
           $table->float('salary_rate')->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('leave_day_reasons', function (Blueprint $table) {
            $table->float('salary_rate')->default(NULL)->change();
        });
    }
}
