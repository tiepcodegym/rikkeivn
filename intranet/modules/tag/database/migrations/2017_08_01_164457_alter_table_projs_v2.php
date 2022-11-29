<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableProjsV2 extends Migration
{
    protected $tbl = 'projs';
    
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
            if (!Schema::hasColumn($this->tbl, 'tag_status')) {
                $table->tinyInteger('tag_status')->default(3); //1. approve, 2. not review, 3. not submit
            }
            if (!Schema::hasColumn($this->tbl, 'tag_assignee')) {
                $table->unsignedInteger('tag_assignee')->nullable();
                $table->foreign('tag_assignee')->references('id')->on('employees')
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
        
    }
}
