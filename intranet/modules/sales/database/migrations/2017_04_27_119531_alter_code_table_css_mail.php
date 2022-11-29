<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterCodeTableCssMail extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('css_mail') ||
            !Schema::hasColumn('css_mail', 'code')) {
            return;
        }
        Schema::table('css_mail', function (Blueprint $table) {
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
        Schema::table('css_mail', function (Blueprint $table) {
            $table->tinyInteger('code')->default(0)->change();
        });
    }
}
