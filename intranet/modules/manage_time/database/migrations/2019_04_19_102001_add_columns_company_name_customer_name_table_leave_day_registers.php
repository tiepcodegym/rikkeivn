<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Rikkei\Resource\View\getOptions;

class AddColumnsCompanyNameCustomerNameTableLeaveDayRegisters extends Migration
{
    private $tbl = 'leave_day_registers';
    private $col1 = 'company_name';
    private $col2 = 'customer_name';

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
        if (!Schema::hasColumn($this->tbl, $this->col1)) {
            Schema::table($this->tbl, function(Blueprint $table) {
                $table->string($this->col1)->nullable();
            });
        }
        if (!Schema::hasColumn($this->tbl, $this->col2)) {
            Schema::table($this->tbl, function(Blueprint $table) {
                $table->string($this->col2)->nullable();
            });
        }
        
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
