<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableEmployeeContactAddColumDontReceiveSystemMail extends Migration
{
    protected $tbl = 'employee_contact';
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable($this->tbl) || Schema::hasColumn($this->tbl, 'dont_receive_system_mail')) {
            return;
        }
        Schema::table($this->tbl, function (Blueprint $table) {
            $table->boolean('dont_receive_system_mail')->default(0)->comment("0 => Receive system mail, 1 => Don't receive system mail");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable($this->tbl) && Schema::hasColumn($this->tbl, 'dont_receive_system_mail')) {
            Schema::table($this->tbl, function (Blueprint $table) {
                $table->dropColumn('dont_receive_system_mail');
            });
        }
    }
}
