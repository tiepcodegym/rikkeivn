<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterCertificatesV1 extends Migration
{
    protected $tbl = 'certificates';
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
            if (Schema::hasColumn($this->tbl, 'type')) {
                $table->dropColumn('type');
            }
            if (Schema::hasColumn($this->tbl, 'image')) {
                $table->dropColumn('image');
            }
            if (Schema::hasColumn($this->tbl, 'created_by')) {
                $table->dropColumn('created_by');
            }
            if (Schema::hasColumn($this->tbl, 'created_at')) {
                $table->dateTime('created_at')->nullable()->change();
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
