<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAddColumnHrmContractIdInContractsTable extends Migration
{
    private $table = 'contracts';
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
            $table->string('hrm_contract_id')->nullable()->after('created_id');
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
            $table->dropColumn('hrm_contract_id');
        });
    }
}
