<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableUsersAddColumnExpiresIn extends Migration
{
    protected $tbl = 'users';

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
            if (!Schema::hasColumn($this->tbl, 'expires_in')) {
                $table->integer('expires_in')->nullable();
            }
            if (!Schema::hasColumn($this->tbl, 'refresh_token')) {
                $table->string('refresh_token')->after('token')->nullable();
            }
            if (!Schema::hasColumn($this->tbl, 'im_token')) {
                $table->string('im_token')->nullable();
            }
            if (!Schema::hasColumn($this->tbl, 'im_user_id')) {
                $table->string('im_user_id', 32)->nullable();
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
