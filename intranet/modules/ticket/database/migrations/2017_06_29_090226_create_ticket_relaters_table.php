<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTicketRelatersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasTable('ticket_relaters'))
        {
            return;
        }

        Schema::create('ticket_relaters', function (Blueprint $table) {
            $table->unsignedInteger('ticket_id');
            $table->unsignedInteger('employee_id');

            $table->primary(['ticket_id', 'employee_id']);

            $table->foreign('ticket_id')->references('id')->on('tickets');
            $table->foreign('employee_id')->references('id')->on('employees');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('ticket_relaters');
    }
}
