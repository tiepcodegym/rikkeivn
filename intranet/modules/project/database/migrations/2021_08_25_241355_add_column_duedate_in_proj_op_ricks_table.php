<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnDuedateInProjOpRicksTable extends Migration
{
    protected $table = 'proj_op_ricks';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable($this->table)) {
            return;
        }
        Schema::table($this->table, function (Blueprint $table) {
            $table->date('due_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (!Schema::hasTable($this->table)) {
            return;
        }
        Schema::table($this->table, function (Blueprint $table) {
            $table->dropColumn('due_date');
        });
    }
}
