<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableEventBirthCust extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('event_birth_cust')) {
            return false;
        }
        if (!Schema::hasColumn('event_birth_cust', 'sender_name')) {
            Schema::table('event_birth_cust', function (Blueprint $table) {
                $table->string('sender_name')->nullable();
            });
        }
        if (!Schema::hasColumn('event_birth_cust', 'sender_email')) {
            Schema::table('event_birth_cust', function (Blueprint $table) {
                $table->string('sender_email')->nullable();
            });
        }
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
