<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableEventBirthCustV1 extends Migration
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
        if (!Schema::hasColumn('event_birth_cust', 'lang')) {
            Schema::table('event_birth_cust', function (Blueprint $table) {
                $table->string('lang', 3)->nullable();
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
