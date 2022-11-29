<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSomeColumnTableEmployees extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        Schema::table('employees', function (Blueprint $table) {
            if ( !Schema::hasColumn('employees', 'passport_number') ) {
                $table->string('passport_number',150)->nullable();
            }
            if ( !Schema::hasColumn('employees', 'passport_date_start') ) {
                $table->dateTime('passport_date_start')->nullable();
            }
            if ( !Schema::hasColumn('employees', 'passport_date_exprie') ) {
                $table->dateTime('passport_date_exprie')->nullable();
            }
            if ( !Schema::hasColumn('employees', 'passport_addr') ) {
                $table->string('passport_addr')->nullable();
            }
            if ( !Schema::hasColumn('employees', 'marital') ) {
                $table->smallInteger('marital')->nullable();
            }
            
            if ( !Schema::hasColumn('employees', 'folk') ) {
                $table->string('folk')->nullable();
            }
            
            if ( !Schema::hasColumn('employees', 'religion') ) {
                $table->string('religion')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('employees', function (Blueprint $table) {
            if ( Schema::hasColumn('employees', 'passport_number') ) {
                $table->dropColumn('passport_number');
            }
            if ( Schema::hasColumn('employees', 'passport_date_start') ) {
                $table->dropColumn('passport_date_start');
            }
            if ( Schema::hasColumn('employees', 'passport_date_exprie') ) {
                $table->dropColumn('passport_date_exprie');
            }
            if ( Schema::hasColumn('employees', 'passport_addr') ) {
                $table->dropColumn('passport_addr');
            }
            if ( Schema::hasColumn('employees', 'marital') ) {
                $table->dropColumn('marital');
            }
            
            if ( Schema::hasColumn('employees', 'folk') ) {
                $table->dropColumn('folk');
            }
            
            if ( Schema::hasColumn('employees', 'religion') ) {
                $table->dropColumn('religion');
            }
        });
    }
}
