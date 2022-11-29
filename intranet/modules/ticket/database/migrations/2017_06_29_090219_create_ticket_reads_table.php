<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTicketReadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasTable('ticket_reads'))
        {
            return;
        }

        Schema::create('ticket_reads', function (Blueprint $table) {
            $table->unsignedInteger('ticket_id');
            $table->unsignedInteger('reader_id');
            $table->boolean('status')->default(0);

            $table->primary(['ticket_id', 'reader_id']);

            $table->foreign('ticket_id')->references('id')->on('tickets');
            $table->foreign('reader_id')->references('id')->on('employees');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('ticket_reads');
    }
}
