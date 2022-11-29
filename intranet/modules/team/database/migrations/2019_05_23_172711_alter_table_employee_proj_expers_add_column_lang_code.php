<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableEmployeeProjExpersAddColumnLangCode extends Migration
{
    protected $tbl = 'employee_proj_expers';
    protected $col = 'lang_code';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasColumn($this->tbl, $this->col)) {
            return;
        }
        Schema::table($this->tbl, function (Blueprint $table) {
            $table->string('lang_code', 2)->nullable()->after('sort_order');
            $table->unsignedInteger('en_id')->nullable()->after('lang_code');
            $table->foreign('en_id')
                    ->references('id')
                    ->on($this->tbl)
                    ->onDelete('set null');
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
