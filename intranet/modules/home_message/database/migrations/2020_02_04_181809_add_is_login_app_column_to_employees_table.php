<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Rikkei\Notify\Classes\RkNotify;

class AddIsLoginAppColumnToEmployeesTable extends Migration
{
    protected $tbl = 'employees';
    protected $column = 'is_login_app';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable($this->tbl) || Schema::hasColumn($this->tbl, $this->column)) {
            return;
        }
        Schema::table($this->tbl, function (Blueprint $table) {
            $table->tinyInteger($this->column)->default(0)->comment('Dùng để phục vụ cho việc kiểm tra user đã đăng nhập app hay chưa để thưởng point lễ tết'); //
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
