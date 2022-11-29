<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableMeAttributesV1 extends Migration
{
    protected $tbl = 'me_attributes';
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable($this->tbl)) {
            return;
        }
        if (Schema::hasColumn($this->tbl, 'has_na')) {
            return;
        }
        Schema::table($this->tbl, function (Blueprint $table) {
           $table->boolean('has_na'); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (!Schema::hasTable($this->tbl)) {
            return;
        }
        if (!Schema::hasColumn($this->tbl, 'has_na')) {
            return;
        }
        Schema::table($this->tbl, function (Blueprint $table) {
           $table->dropColumn('has_na'); 
        });
    }
}
