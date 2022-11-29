<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AddColumnCategoryInProjsTable extends Migration
{
    protected $table = 'projs';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable($this->table)) {
            return;
        }
        Schema::table($this->table, function (Blueprint $table) {
            $table->tinyInteger('category_id')->nullable();
            $table->tinyInteger('classification_id')->nullable();
            $table->tinyInteger('business_id')->nullable();
            $table->tinyInteger('sub_sector')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (!Schema::hasTable($this->table)) {
            return;
        }
        Schema::table($this->table, function (Blueprint $table) {
            $table->dropColumn('category_id');
            $table->dropColumn('classification_id');
            $table->dropColumn('business_id');
            $table->dropColumn('sub_sector');
        });
    }
}
