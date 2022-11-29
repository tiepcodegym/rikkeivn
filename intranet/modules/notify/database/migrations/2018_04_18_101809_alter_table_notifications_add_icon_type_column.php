<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableNotificationsAddIconTypeColumn extends Migration
{
    protected $tbl = 'notifications';
    protected $column = 'icon';

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
        if (Schema::hasColumn($this->tbl, $this->column)) {
            return;
        }
        Schema::table($this->tbl, function (Blueprint $table) {
            $table->string($this->column, 32)->nullable()->after('schedule_code');
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
