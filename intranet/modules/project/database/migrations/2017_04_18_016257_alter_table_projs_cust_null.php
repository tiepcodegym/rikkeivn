<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableProjsCustNull extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tbl = 'projs';
        if (!Schema::hasTable($tbl) || !Schema::hasColumn($tbl, 'cust_contact_id')) {
            return;
        }
        Schema::table($tbl, function (Blueprint $table) {
           $table->unsignedInteger('cust_contact_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
