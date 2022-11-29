<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterBusinessTripV2 extends Migration
{
    protected $tbl = 'business_trip_registers';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable($this->tbl)) {
            return true;
        }
        Schema::table($this->tbl, function (Blueprint $table) {
            $table->unsignedInteger('country_id')->nullable()
                ->comment('foreign table lib_contrry');
            $table->unsignedInteger('province_id')->nullable()
                ->comment('foreign table lib_province');
            $table->boolean('is_long')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {}
}
