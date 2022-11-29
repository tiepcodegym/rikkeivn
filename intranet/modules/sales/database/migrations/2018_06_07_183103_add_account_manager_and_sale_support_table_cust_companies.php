<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAccountManagerAndSaleSupportTableCustCompanies extends Migration
{
    private $tbl = 'cust_companies';
    private $col1 = 'manager_id';
    private $col2 = 'sale_support_id';

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
                $table->unsignedInteger($this->col1)->nullable();
            }
            if (!Schema::hasColumn($this->tbl, $this->col2)) {
                $table->unsignedInteger($this->col2)->nullable();
            }

            $table->foreign($this->col1)->references('id')->on('employees')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
            $table->foreign($this->col2)->references('id')->on('employees')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
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
