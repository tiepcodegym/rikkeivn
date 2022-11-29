<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmployeeContact extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('employee_contact')) {
            return;
        }
        Schema::create('employee_contact', function (Blueprint $table) {
            $table->unsignedInteger('employee_id');
            $table->string('mobile_phone', 20)->nullable();
            $table->string('office_phone', 20)->nullable();
            $table->string('home_phone', 20)->nullable();
            $table->string('other_phone', 20)->nullable();
            $table->string('personal_email', 100)->nullable();
            $table->string('other_email', 100)->nullable();
            $table->string('facebook', 100)->nullable();
            $table->string('skype', 100)->nullable();
            $table->string('yahoo',100)->nullable();
            
            $table->string('native_addr')->nullable();
            $table->string('native_country',100)->nullable();
            $table->string('native_province',100)->nullable();
            $table->string('native_district',100)->nullable();
            $table->string('native_ward', 100)->nullable();
            
            $table->string('tempo_addr')->nullable();
            $table->string('tempo_country',100)->nullable();
            $table->string('tempo_province',100)->nullable();
            $table->string('tempo_district',100)->nullable();
            $table->string('tempo_ward', 100)->nullable();
            
            $table->string('emergency_contact_name', 100)->nullable();
            $table->smallInteger('emergency_relationship')->nullable();
            $table->string('emergency_mobile',20)->nullable();
            $table->string('emergency_contact_mobile',20)->nullable();
            $table->string('emergency_addr')->nullable();
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
        Schema::drop('employee_contact');
    }
}
