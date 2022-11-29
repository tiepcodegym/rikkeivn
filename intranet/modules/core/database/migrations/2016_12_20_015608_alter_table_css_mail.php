<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableCssMail extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('css_mail', function($table) {
            $table->dropForeign('css_mail_css_id_foreign');
            $table->dropUnique('css_mail_css_id_unique');
            $table->foreign('css_id')
                ->references('id')
                ->on('css');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('css_mail', function($table) {
            $table->unique('css_id');
        });
    }
}
