<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTypeTableCompany extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('cust_companies') ||
            Schema::hasColumn('cust_companies', 'type')) {
            return;
        }
        Schema::table('cust_companies', function (Blueprint $table) {
            $table->tinyInteger('type')->default(0)->comment('1: systena');
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
            $table->drop('type');
        });
    }
}
