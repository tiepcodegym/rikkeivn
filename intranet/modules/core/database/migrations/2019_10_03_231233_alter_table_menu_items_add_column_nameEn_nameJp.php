<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableMenuItemsAddColumnNameEnNameJp extends Migration
{
    protected $table = 'menu_items';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table($this->table, function(Blueprint $table) {
            $table->string('en_name')->nullable();
            $table->string('ja_name')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table($this->table, function(Blueprint $table) {
            $table->dropColumn('en_name');
            $table->dropColumn('ja_name');
        });
    }
}
