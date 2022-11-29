<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmployeeCostume extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasTable('employee_costume')) {
            return;
        }
        Schema::create('employee_costume', function (Blueprint $table) {
            $table->unsignedInteger('employee_id');
            $table->string('asia_shirts', 5)->nullable();
            $table->string('asia_paints', 5)->nullable();
            $table->string('asia_zuyp', 5)->nullable();
            $table->string('asia_protective', 5)->nullable();
            $table->integer('euro_shirts')->nullable();
            $table->integer('euro_paints')->nullable();
            $table->integer('euro_zuyp')->nullable();
            $table->integer('euro_protective')->nullable();

            $table->float('shoudler_width')->nullable();
            $table->float('long_sleeve')->nullable();
            $table->float('long_shirt')->nullable();
            $table->float('round_chest')->nullable();
            $table->float('round_waist')->nullable();
            $table->float('round_butt')->nullable();
            $table->float('long_pants')->nullable();
            $table->float('long_skirt')->nullable();
            $table->float('round_thigh')->nullable();

            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();

            $table->primary('employee_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('employee_costume');
    }
}
