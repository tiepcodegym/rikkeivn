<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeColumnsInTableWelfares extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('welfares', function(Blueprint $table)
        {
            $table->dateTime('start_at_register')->nullable()->change();
            $table->dateTime('end_at_register')->nullable()->change();
            $table->integer('wel_purpose_id')->nullable()->unsigned()->change();
            $table->integer('welfare_group_id')->nullable()->unsigned()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('welfares', function(Blueprint $table)
        {
            $table->dateTime('start_at_register')->change();
            $table->dateTime('end_at_register')->change();
            $table->integer('wel_purpose_id')->unsigned()->change();
            $table->integer('welfare_group_id')->unsigned()->change();
        });
    }
}
