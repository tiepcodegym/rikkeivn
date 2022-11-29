<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsTypeToTableMeAttributes extends Migration
{
    protected $tableName = 'me_attributes';
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable($this->tableName)) {
            return;
        }
        if (Schema::hasColumn($this->tableName, 'type')) {
            return;
        }
        Schema::table($this->tableName, function (Blueprint $table) {
           $table->unsignedTinyInteger('type')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (!Schema::hasTable($this->tableName)) {
            return;
        }
        if (!Schema::hasColumn($this->tableName, 'type')) {
            return;
        }
        Schema::table($this->tableName, function (Blueprint $table) {
           $table->dropColumn('type'); 
        });
    }
}
