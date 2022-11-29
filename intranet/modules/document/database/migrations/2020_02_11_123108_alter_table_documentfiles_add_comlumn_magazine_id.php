<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableDocumentfilesAddComlumnMagazineId extends Migration
{
    protected $tbl = 'documentfiles';
    protected $col = 'magazine_id';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable($this->tbl) || Schema::hasColumn($this->tbl, $this->col)) {
            return;
        }
        Schema::table($this->tbl, function (Blueprint $table) {
            $table->unsignedInteger($this->col)->after('author_id')->nullable();
            $table->foreign($this->col)->references('id')->on('magazine')->onUpdate('cascade')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table($this->tbl, function (Blueprint $table) {
            $table->dropColumn($this->col);
        });
    }
}
