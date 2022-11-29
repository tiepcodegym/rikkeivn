<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableEmployeePrograms extends Migration
{
    protected $tbl = 'employee_programs';
    
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
            $table->bigIncrements('id');
            $table->unsignedInteger('employee_id');
            $table->unsignedInteger('programming_id');
            $table->tinyInteger('level');
            $table->tinyInteger('experience');
            $table->timestamps();
            $table->foreign('employee_id')->references('id')->on('employees')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
            $table->foreign('programming_id')->references('id')->on('programming_languages')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
            $table->unique(['employee_id', 'programming_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists($this->tbl);
    }
}
