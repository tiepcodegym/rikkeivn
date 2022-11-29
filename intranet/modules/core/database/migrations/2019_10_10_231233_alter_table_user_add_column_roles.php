<?php
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableUserAddColumnRoles extends Migration
{
    protected $table = 'users';
    protected $col = 'roles';

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
            $table->longText($this->col)->nullable()->comment('Lưu cache role của nhân viên');
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
