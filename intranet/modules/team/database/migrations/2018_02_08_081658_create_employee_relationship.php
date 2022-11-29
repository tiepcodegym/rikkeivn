<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmployeeRelationship extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('employee_relationship')) {
            return;
        }
        Schema::create('employee_relationship', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('employee_id');
            $table->smallInteger('relationship');
            $table->string('name');
            $table->string('date_of_birth', 10)->nullable();
            $table->string('national', 50)->nullable();
            $table->string('id_number', 100)->nullable();
            $table->string('address')->nullable();
            $table->string('mobile', 20)->nullable();
            $table->string('tel', 20)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('tax_code', 100)->nullable();
            $table->string('career')->nullable();
            $table->string('working_place')->nullable();
            $table->boolean('is_dependent')->nullable();
            $table->date('deduction_start_date')->nullable();
            $table->dateTime('deduction_end_date')->nullable();
            $table->boolean('is_die')->nullable();
            $table->text('note')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();

            $table->index('employee_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('employee_relationship');
    }
}
