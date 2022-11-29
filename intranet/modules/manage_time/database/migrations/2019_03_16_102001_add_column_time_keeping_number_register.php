<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Rikkei\Resource\View\getOptions;

class AddColumnTimeKeepingNumberRegister extends Migration
{
    private $tbl = 'manage_time_timekeepings';
    private $col = 'timekeeping_number_register';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable($this->tbl)
                || Schema::hasColumn($this->tbl, $this->col)) {
            return;
        }
        Schema::table($this->tbl, function(Blueprint $table) {
            $table->float($this->col);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (!Schema::hasTable($this->tbl)
                || !Schema::hasColumn($this->tbl, $this->col1)) {
            return;
        }
        Schema::table($this->tbl, function (Blueprint $table) {
            $table->dropColumn($this->col1); 
        });
    }
}
