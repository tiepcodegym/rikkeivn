<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddProjsIdColumnToOtRegistersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ot_registers', function (Blueprint $table) {
            $table->integer('projs_id')->unsigned()->nullable()->after('employee_id');
            $table->foreign('projs_id')->references('id')->on('projs')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ot_registers', function (Blueprint $table) {
            $table->dropColumn('projs_id');
            $table->dropForeign('ot_registers_projs_id_foreign');
        });
    }
}
