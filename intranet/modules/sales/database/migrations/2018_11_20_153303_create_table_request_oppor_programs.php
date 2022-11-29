<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableRequestOpporPrograms extends Migration
{
    protected $tbl = 'request_oppor_programs';
    protected $tblrq = 'request_opportunities';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable($this->tbl)) {
            return;
        }
        Schema::create($this->tbl, function (Blueprint $table) {
            $table->unsignedInteger('req_oppor_id');
            $table->unsignedInteger('prog_id');
            $table->primary(['req_oppor_id', 'prog_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists($this->tbl);
    }
}
