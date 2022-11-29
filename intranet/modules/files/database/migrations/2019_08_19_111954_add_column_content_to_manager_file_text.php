<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnContentToManagerFileText extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('manage_file_text', function($table) {
            $table->longtext('content')->nullable()->comment = 'nội dung văn bản';
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('manage_file_text', function($table) {
            $table->dropColumn('content');
        });
    }
}
