<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableCompany extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('cust_companies') ||
            Schema::hasColumn('cust_companies', 'name_ja')) {
            return;
        }
        Schema::table('cust_companies', function (Blueprint $table) {
            $table->string('name_ja')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
