<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnScopeCustomerRequireTableProjectMetas extends Migration
{
    protected $tbl = 'project_metas';
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
        if (Schema::hasColumn($this->tbl, 'scope_customer_require')) {
            return;
        }
        Schema::table($this->tbl, function (Blueprint $table) {
           $table->text('scope_customer_require')->nullable(); 
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
        if (!Schema::hasColumn($this->tbl, 'scope_customer_require')) {
            return;
        }
        Schema::table($this->tbl, function (Blueprint $table) {
           $table->dropColumn('scope_customer_require'); 
        });
    }
}
