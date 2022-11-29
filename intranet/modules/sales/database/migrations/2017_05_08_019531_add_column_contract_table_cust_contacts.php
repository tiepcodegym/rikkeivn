<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnContractTableCustContacts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('cust_contacts') ||
            Schema::hasColumn('cust_contacts', 'contract')) {
            return;
        }
        Schema::table('cust_contacts', function (Blueprint $table) {
            $table->text('contract')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (!Schema::hasTable('cust_contacts')) {
            return;
        }
        if (!Schema::hasColumn('cust_contacts', 'contract')) {
            return;
        }
        Schema::table('cust_contacts', function (Blueprint $table) {
            $table->dropColumn('contract'); 
        });
    }
}
