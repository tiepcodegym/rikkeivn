<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableCustCompaniesAddColumnCrmAccountId extends Migration
{
    protected $tbl = 'cust_companies';
    protected $col = ['crm_account_id'];

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
            foreach ($this->col as $col) {
                if (!Schema::hasColumn($this->tbl, $col)) {
                    $table->char($col, 36)->nullable();
                }
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
        //
    }
}
