<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnNoteOfEmpToManagerAssetItemTbl extends Migration
{
    protected $sTbl = 'manage_asset_items';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable($this->sTbl)) {
            return true;
        }
        Schema::table($this->sTbl, function (Blueprint $table) {
            if (!Schema::hasColumn($this->sTbl, 'note_of_emp')) {
                $table->text('note_of_emp')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (!Schema::hasTable($this->sTbl)
        || !Schema::hasColumn($this->sTbl, 'note_of_emp')) {
            return;
        }
        Schema::table($this->sTbl, function (Blueprint $table) {
            $table->dropColumn('note_of_emp');
        });
    }
}
