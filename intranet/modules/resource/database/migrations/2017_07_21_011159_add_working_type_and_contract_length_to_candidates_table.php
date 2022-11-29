<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddWorkingTypeAndContractLengthToCandidatesTable extends Migration
{
    private $table = 'candidates';
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table($this->table, function (Blueprint $table) {
            $table->tinyInteger('working_type')->comment('0: not set;\n1: trainee;\n2: parttime\n3: fulltime');
            $table->string('contract_length', 100)->after('working_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table($this->table, function (Blueprint $table) {
            $table->dropColumn('working_type');
            $table->dropColumn('contract_length');
        });
    }
}
