<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableCssResult extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('css_result') ||
            !Schema::hasColumn('css_result', 'code')) {
            return;
        }
        Schema::table('css_result', function (Blueprint $table) {
            $table->string('code', 255)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('css_result', function (Blueprint $table) {
            $table->tinyInteger('code')->default(0)->change();
        });
    }
}
