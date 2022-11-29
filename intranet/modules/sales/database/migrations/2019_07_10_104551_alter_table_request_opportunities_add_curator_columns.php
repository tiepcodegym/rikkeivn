<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableRequestOpportunitiesAddCuratorColumns extends Migration
{
    protected $tbl = 'request_opportunities';
    protected $col = ['curator', 'curator_email'];

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
                    $table->string($col)->nullable()->after('customer_name');
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
