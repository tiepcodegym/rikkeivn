<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnEmployeeCostAndAddColumnCompanyCosts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('wel_employee', function (Blueprint $table) {
            $table->float('cost_employee', 15, 2);
            $table->float('cost_company', 15, 2);

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('wel_employee', function (Blueprint $table) {
            $table->dropColumn('cost_employee');
            $table->dropColumn('cost_company');

        });
    }
}
