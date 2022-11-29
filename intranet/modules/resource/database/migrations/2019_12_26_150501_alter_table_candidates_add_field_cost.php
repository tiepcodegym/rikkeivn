<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableCandidatesAddFieldCost extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('candidates') || Schema::hasColumn('candidates', 'cost')) {
            return;
        }

        Schema::table('candidates', function (Blueprint $table) {
            $table->bigInteger('cost')->nullable()->comment('chi phí khi thuộc loại kênh thay đổi');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (!Schema::hasTable('candidates') || !Schema::hasColumn('candidates', 'cost')) {
            return;
        }

        Schema::table('candidates', function (Blueprint $table) {
            $table->dropColumn('cost');
        });
    }
}
