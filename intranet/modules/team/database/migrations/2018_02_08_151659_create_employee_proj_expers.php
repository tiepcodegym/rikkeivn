<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmployeeProjExpers extends Migration
{
    protected $tbl = 'employee_proj_expers';

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
            $table->increments('id');
            $table->unsignedInteger('employee_id');
//            $table->unsignedInteger('company_id')->nullable();
//            $table->string('name');
//            $table->string('customer')->nullable();
//            $table->string('position')->nullable();
//            $table->unsignedSmallInteger('no_member')->nullable();
//            $table->string('env')->nullable();
            $table->date('start_at')->nullable();
            $table->date('end_at')->nullable();
            $table->integer('sort_order')->nullable();
//            $table->string('period', 10)->nullable()
//                ->comment('3-10: 3 year - 10 month');
//            $table->text('other_tech')->nullable();
//            $table->string('responsible')->nullable();
//            $table->text('description')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();

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
        Schema::drop($this->tbl);
    }
}
