<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableEmployeesAddColumnCacheVersion extends Migration
{
    protected $table = 'employees';
    protected $col = 'cache_version';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable($this->table) || Schema::hasColumn($this->table, $this->col)) {
            return;
        }
        Schema::table($this->table, function (Blueprint $table) {
            $table->bigInteger($this->col)->nullable()->comment('Đánh dấu version khi thay đổi quyền hoặc thông tin nhân viên hỗ trợ refresh cache liên quan');
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
