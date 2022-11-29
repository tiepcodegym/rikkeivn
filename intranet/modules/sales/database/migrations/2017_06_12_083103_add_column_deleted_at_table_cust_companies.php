<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnDeletedAtTableCustCompanies extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('cust_companies') ||
            Schema::hasColumn('cust_companies', 'deleted_at')) {
            return;
        }
        Schema::table('cust_companies', function ($table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (!Schema::hasTable('cust_companies')) {
            return;
        }
        if (!Schema::hasColumn('cust_companies', 'deleted_at')) {
            return;
        }
        Schema::table('cust_companies', function (Blueprint $table) {
            $table->dropColumn('deleted_at'); 
        });
    }
}
