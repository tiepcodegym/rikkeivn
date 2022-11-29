<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnDeletedAtTableCustContacts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('cust_contacts') ||
            Schema::hasColumn('cust_contacts', 'deleted_at')) {
            return;
        }
        Schema::table('cust_contacts', function ($table) {
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
        if (!Schema::hasTable('cust_contacts')) {
            return;
        }
        if (!Schema::hasColumn('cust_contacts', 'deleted_at')) {
            return;
        }
        Schema::table('cust_contacts', function (Blueprint $table) {
            $table->dropColumn('deleted_at'); 
        });
    }
}
