<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTypeCertificateTable extends Migration
{
    protected $tbl = 'certificates';
    protected $col = 'type';
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable($this->tbl) && !Schema::hasColumn($this->tbl, $this->col)) {
            Schema::table($this->tbl, function (Blueprint $table) {
                 $table->integer($this->col)->after('id')->nullable();
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
        if (Schema::hasTable($this->tbl) && Schema::hasColumn($this->tbl, $this->col)) {
            Schema::table($this->tbl, function (Blueprint $table) {
                $table->dropColumn($this->col);
            });
        }
    }
}
