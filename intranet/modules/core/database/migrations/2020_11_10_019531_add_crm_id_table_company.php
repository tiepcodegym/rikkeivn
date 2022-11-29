<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCrmIdTableCompany extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('cust_companies') ||
            Schema::hasColumn('cust_companies', 'crm_id')) {
            return;
        }
        Schema::table('cust_companies', function (Blueprint $table) {
            $table->string('crm_id', 50)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cust_companies', function (Blueprint $table) {
            $table->drop('crm_id');
        });
    }
}
