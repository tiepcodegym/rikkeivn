<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableMeActivities extends Migration
{
    protected $tbl = 'me_activities';

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
            $table->string('month', 8);
            $table->unsignedInteger('employee_id');
            $table->unsignedInteger('attr_id');
            $table->text('content')->nullable();
            $table->index(['month', 'employee_id']);
            $table->timestamps();
            $table->foreign('employee_id')
                    ->references('id')
                    ->on('employees')
                    ->onDelete('cascade');
            $table->foreign('attr_id')
                    ->references('id')
                    ->on('me_attributes')
                    ->onDelete('cascade');
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
