<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterSchoolsV1 extends Migration
{
    protected $tbl = 'schools';
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
        Schema::table($this->tbl, function (Blueprint $table) {
            if (!Schema::hasColumn($this->tbl, 'sort_order')) {
                $table->integer('sort_order')->nullable();
            }
            if (Schema::hasColumn($this->tbl, 'country')) {
                $table->dropColumn('country');
            }
            if (Schema::hasColumn($this->tbl, 'province')) {
                $table->dropColumn('province');
            }
            if (Schema::hasColumn($this->tbl, 'image')) {
                $table->dropColumn('image');
            }
            if (Schema::hasColumn($this->tbl, 'created_at')) {
                $table->dropColumn('created_at');
            }
            if (Schema::hasColumn($this->tbl, 'updated_at')) {
                $table->dropColumn('updated_at');
            }
            if (Schema::hasColumn($this->tbl, 'created_by')) {
                $table->dropColumn('created_by');
            }
            if (Schema::hasColumn($this->tbl, 'deleted_at')) {
                $table->dropColumn('deleted_at');
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
    }
}
