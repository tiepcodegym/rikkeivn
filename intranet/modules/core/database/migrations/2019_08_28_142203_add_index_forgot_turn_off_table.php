<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIndexForgotTurnOffTable extends Migration
{
    protected $table = 'forgot_turn_off';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table($this->table, function(Blueprint $table) {
            $table->unique(['employee_id', 'forgot_date'], 'index_forgot_date_unique' );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table($this->table, function(Blueprint $table) {
            $table->dropUnique('index_forgot_date_unique');
        });
    }
}
