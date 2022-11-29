<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnGenderCssMailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('css_mail') || Schema::hasColumn('css_mail', 'gender')) {
            return;
        }

        Schema::table('css_mail', function (Blueprint $table) {
            $table->tinyInteger('gender')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (!Schema::hasTable('css_mail')) {
            return;
        }
        if (!Schema::hasColumn('css_mail', 'gender')) {
            return;
        }

        Schema::table('css_mail', function (Blueprint $table) {
            $table->dropColumn('gender');
        });
    }
}
