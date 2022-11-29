<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnStatusTableCssResult extends Migration
{
    protected $table = 'css_result';
    protected $columnStatus = 'status';
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable($this->table)) {
            return;
        }
        Schema::table('css_result', function (Blueprint $table) {
            if (!Schema::hasColumn($this->table, $this->columnStatus)) {
                $table->integer($this->columnStatus)->after('avg_point')->nullable();
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
        Schema::table('css_result', function (Blueprint $table) {
            if (Schema::hasColumn($this->table, $this->columnStatus)) {
                $table->dropColumn($this->columnStatus);
            }
        });
    }
}
