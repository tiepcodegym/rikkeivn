<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableNtestTypes extends Migration
{
    
    protected $tbl = 'ntest_types';
    
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
            if (!Schema::hasColumn($this->tbl, 'parent_id')) {
                $table->unsignedInteger('parent_id')
                        ->nullable()
                        ->after('name');
                $table->foreign('parent_id')
                        ->references('id')
                        ->on($this->tbl)
                        ->onUpdate('cascade')
                        ->onDelete('set null');
            }
            if (!Schema::hasColumn($this->tbl, 'code')) {
                $table->string('code', 32)->nullable()->after('name');
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
