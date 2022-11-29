<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterCustContacts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cust_contacts', function (Blueprint $table) {
            $table->unsignedInteger('company_id')->nullable()->change();
            $table->text('address')->change();
            $table->string('image', 255)->nullable()->change();
            $table->text('note')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cust_contacts', function (Blueprint $table) {
            //
        });
    }
}
