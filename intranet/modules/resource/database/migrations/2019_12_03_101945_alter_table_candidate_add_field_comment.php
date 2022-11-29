<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableCandidateAddFieldComment extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('candidates') || Schema::hasColumn('candidates', 'comment')) {
            return;
        }

        Schema::table('candidates', function (Blueprint $table) {
            $table->string('comment')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (!Schema::hasTable('candidates') || !Schema::hasColumn('candidates', 'comment')) {
            return;
        }

        Schema::table('candidates', function (Blueprint $table) {
            $table->dropColumn('comment');
        });
    }
}
