<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterEmployeesCardId extends Migration
{
    private $table = 'employees';
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable($this->table) || !Schema::hasColumn($this->table, 'employee_card_id')) {
            return;
        }
        Schema::table($this->table, function(Blueprint $table) {
            $table->unsignedInteger('employee_card_id')->nullable()->change();
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
