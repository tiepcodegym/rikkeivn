<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SetNullableColumnDeletedAtCsstable extends Migration
{
    protected $tbl = 'css';
    protected $col = 'deleted_at';
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       if (Schema::hasTable($this->tbl) && Schema::hasColumn($this->tbl, $this->col)) {
           Schema::table($this->tbl, function (Blueprint $table) {
               $table->dateTime($this->col)->nullable()->change();
           });
       }
       if (Schema::hasTable('css_result') && Schema::hasColumn('css_result', $this->col)) {
           Schema::table('css_result', function (Blueprint $table) {
                $table->dateTime($this->col)->nullable()->change();
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
        //
    }
}
