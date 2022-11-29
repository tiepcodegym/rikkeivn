<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBusinessTripRegistersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('business_trip_registers')) {
            return;
        }

        Schema::create('business_trip_registers', function(Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('creator_id');
            $table->unsignedInteger('approver_id');
            $table->datetime('date_start');
            $table->datetime('date_end');
            $table->double('number_days_business_trip', 8, 2);
            $table->string('location', 255);
            $table->string('company_customer', 255)->nullable();
            $table->text('purpose');
            $table->tinyInteger('status')->default(1);
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();

            $table->foreign('creator_id')->references('id')->on('employees');
            $table->foreign('approver_id')->references('id')->on('employees');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('business_trip_registers');
    }
}
