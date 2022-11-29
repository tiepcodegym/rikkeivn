<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangePriceToTextApprove extends Migration
{
    private $table = 'project_approved_production_cost';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $column = 'price';
        if (!Schema::hasTable($this->table) ||
            !Schema::hasColumn($this->table, $column)) {
            return;
        }
        Schema::table($this->table, function (Blueprint $table) use ($column) {
            $table->text($column)->default(0)->change();
        });
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
