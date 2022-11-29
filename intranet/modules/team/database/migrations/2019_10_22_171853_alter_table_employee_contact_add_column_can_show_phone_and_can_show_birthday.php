<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableEmployeeContactAddColumnCanShowPhoneAndCanShowBirthday extends Migration
{
    protected $tbl = 'employee_contact';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable($this->tbl) || Schema::hasColumn($this->tbl, 'can_show_phone') || Schema::hasColumn($this->tbl, 'can_show_birthday')) {
            return;
        }
        Schema::table($this->tbl, function (Blueprint $table) {
            $table->boolean('can_show_phone')->default(1)->comment("0 => Not show phone, 1 => Show phone");
            $table->tinyInteger('can_show_birthday')->default(1)->comment("0 => Not show birthday, 1 => Show birthday, 2 => Show only year");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable($this->tbl) && Schema::hasColumn($this->tbl, 'can_show_phone') && Schema::hasColumn($this->tbl, 'can_show_birthday')) {
            Schema::table($this->tbl, function (Blueprint $table) {
                $table->dropColumn('can_show_phone');
                $table->dropColumn('can_show_birthday');
            });
        }
    }
}
