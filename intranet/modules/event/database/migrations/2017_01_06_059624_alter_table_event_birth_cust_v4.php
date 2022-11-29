<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableEventBirthCustV4 extends Migration
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
        if (!Schema::hasColumn('event_birth_cust', 'show_tour')) {
            Schema::table('event_birth_cust', function (Blueprint $table) {
                $table->boolean('show_tour')->default(1);
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
