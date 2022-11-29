<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeStatusColumnsInTableWelAttachFee extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    protected $tbl = 'wel_attach_fee';
    public function up()
    {
        if (!Schema::hasTable($this->tbl) ||
            !Schema::hasColumn($this->tbl, 'fee_free_relative') ||
            !Schema::hasColumn($this->tbl, 'fee_free_count') ||
            !Schema::hasColumn($this->tbl, 'fee50_count') ||
            !Schema::hasColumn($this->tbl, 'fee50_relative') ||
            !Schema::hasColumn($this->tbl, 'fee100_relative')) {
            return;
        }
        Schema::table($this->tbl, function (Blueprint $table) {
            $table->string('fee_free_relative')->nullable()->change();
            $table->integer('fee50_count')->nullable()->change();
            $table->integer('fee_free_count')->nullable()->change();
            $table->string('fee50_relative')->nullable()->change();
            $table->string('fee100_relative')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //nothing.
    }
}
