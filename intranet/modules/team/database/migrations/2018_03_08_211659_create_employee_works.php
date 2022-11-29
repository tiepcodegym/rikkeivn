<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmployeeWorks extends Migration
{
    protected $tbl = 'employee_works';
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable($this->tbl)) {
            return;
        }
        Schema::create($this->tbl, function (Blueprint $table) {
            $table->unsignedInteger('employee_id');
            $table->string('tax_code')->nullable();
            $table->string('bank_account')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('insurrance_book')->nullable();
            $table->date('insurrance_date')->nullable();
            $table->float('insurrance_ratio')->nullable();
            $table->tinyInteger('contract_type')->nullable();
            $table->string('insurrance_h_code')->nullable();
            $table->date('insurrance_h_expire')->nullable();
            $table->string('register_examination_place')->nullable();
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
        Schema::drop($this->tbl);
    }
}
