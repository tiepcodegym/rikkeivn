<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAddColumnCssIdAndCodeInCssCategoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('css_category')) {
            return;
        }
        Schema::table('css_category', function (Blueprint $table) {
            $table->unsignedInteger('css_id')->default(0)->after('project_type_id');
            $table->string('code', 255)->default(0)->after('css_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('css_category', function (Blueprint $table) {
            $table->dropColumn('css_id');
            $table->dropColumn('code');
        });
    }
}
