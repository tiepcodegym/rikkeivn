<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnToTicketThreadTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ticket_thread', function(Blueprint $table) {
            if (!Schema::hasColumn('ticket_thread', 'type')) {
                $table->tinyInteger('type')->after('content')->nullable();
            }
            if (!Schema::hasColumn('ticket_thread', 'note')) {
                $table->string('note', 255)->after('type')->nullable();
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
        Schema::table('ticket_thread', function(Blueprint $table) {
            $table->dropColumn('type');
            $table->dropColumn('note');
        });
    }
}
