<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableEventBirthCustV2 extends Migration
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
        if (!Schema::hasColumn('event_birth_cust', 'attacher')) {
            Schema::table('event_birth_cust', function (Blueprint $table) {
                $table->text('attacher')->nullable();
            });
        }
        if (!Schema::hasColumn('event_birth_cust', 'booking_room')) {
            Schema::table('event_birth_cust', function (Blueprint $table) {
                $table->tinyInteger('booking_room')->nullable();
            });
        }
        if (!Schema::hasColumn('event_birth_cust', 'join_tour')) {
            Schema::table('event_birth_cust', function (Blueprint $table) {
                $table->string('join_tour')->nullable();
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
