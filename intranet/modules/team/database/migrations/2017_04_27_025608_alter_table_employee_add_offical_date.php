<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableEmployeeAddOfficalDate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('employees') || 
            Schema::hasColumn('employees', 'offcial_date')
        ) {
            return;
        }
        Schema::table('employees', function(Blueprint $table) {
            $table->dateTime('offcial_date')->after('join_date')->nullable();
        });
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
