<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterSourceServerV1 extends Migration
{
    protected $tbl = 'source_server';
    
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
            if (!Schema::hasColumn($this->tbl, 'id_sonar')) {
                $table->string('id_sonar', 100)->nullable()->after('is_check_svn');
            }
            if (!Schema::hasColumn($this->tbl, 'id_jenkins')) {
                $table->string('id_jenkins')->nullable()->after('is_check_svn');
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
    }
}
