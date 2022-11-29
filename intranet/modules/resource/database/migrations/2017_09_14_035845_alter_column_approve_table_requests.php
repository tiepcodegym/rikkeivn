<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Rikkei\Resource\View\getOptions;

class AlterColumnApproveTableRequests extends Migration
{
    
    protected $tbl = 'requests';
    protected $column = 'approve';


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
           if (!Schema::hasColumn($this->tbl, $this->column)) {
               $table->integer($this->column)->default(getOptions::APPROVE_YET);
           } else {
               $table->integer($this->column)->default(getOptions::APPROVE_YET)->change();
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
