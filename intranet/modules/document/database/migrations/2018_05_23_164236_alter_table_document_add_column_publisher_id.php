<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableDocumentAddColumnPublisherId extends Migration
{
    protected $tbl = 'documents';
    protected $col = 'publisher_id';

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
            if (!Schema::hasColumn($this->tbl, $this->col)) {
                $table->unsignedInteger($this->col)->nullable()->after('author_id');
                $table->foreign($this->col)
                        ->references('id')
                        ->on('employees')
                        ->onUpdate('cascade')
                        ->onDelete('set null');
            }
            if (!Schema::hasColumn($this->tbl, 'note')) {
                $table->text('note')->nullable()->after('status');
            }
            if (!Schema::hasColumn($this->tbl, 'request_id')) {
                $table->unsignedInteger('request_id')->nullable()->after('id');
                $table->foreign('request_id')
                        ->references('id')
                        ->on('documentrequests')
                        ->onDelete('set null');
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
