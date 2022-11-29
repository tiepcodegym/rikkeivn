<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblNewTests extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tbl = 'ntest_tests';
        if (!Schema::hasTable($tbl)) {
            return;
        }
        if (Schema::hasColumn($tbl, 'type_id')) {
            return;
        }
        Schema::table($tbl, function (Blueprint $table) {
            $table->unsignedInteger('type_id')->nullable()->after('description');
            if (Schema::hasTable('ntest_types')) {
                $table->foreign('type_id')->references('id')->on('ntest_types')->onDelete('set null');
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
        
    }
}
