<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnToEmployeesTable extends Migration
{
    protected $emTb = 'employees';
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable($this->emTb)) {
            return;
        }
        if (Schema::hasColumn($this->emTb, 'leader_approved') 
            || Schema::hasColumn($this->emTb, 'account_status')) {
            return;
        }
        Schema::table('employees', function (Blueprint $table) {
            $table->boolean('leader_approved')->default(false)->after('contract_length');
            $table->tinyInteger('account_status')->default(1)->after('leader_approved');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn('leader_approved');
            $table->dropColumn('account_status');
        });
    }
}
