<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsContractsTableCustCompanies extends Migration
{
    private $tbl = 'cust_companies';
    private $col1 = 'contract_security';
    private $col2 = 'contract_quality';
    private $col3 = 'contract_other';

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
            if (!Schema::hasColumn($this->tbl, $this->col1)) {
                $table->text($this->col1)->nullable();
            }
            if (!Schema::hasColumn($this->tbl, $this->col2)) {
                $table->text($this->col2)->nullable();
            }
            if (!Schema::hasColumn($this->tbl, $this->col3)) {
                $table->text($this->col3)->nullable();
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
