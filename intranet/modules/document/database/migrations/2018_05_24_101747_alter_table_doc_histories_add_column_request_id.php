<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableDocHistoriesAddColumnRequestId extends Migration
{
    protected $tbl = 'doc_histories';
    protected $col = 'request_id';

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
        if (Schema::hasColumn($this->tbl, $this->col)) {
            return;
        }
        Schema::table($this->tbl, function (Blueprint $table) {
            $table->unsignedInteger('doc_id')->nullable()->change();
            $table->unsignedInteger($this->col)->nullable()->after('doc_id');
            $table->foreign($this->col)
                    ->references('id')
                    ->on('documentrequests')
                    ->onDelete('cascade');
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
