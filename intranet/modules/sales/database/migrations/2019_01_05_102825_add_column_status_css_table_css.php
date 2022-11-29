<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnStatusCssTableCss extends Migration
{
    protected $table = 'css';
    protected $columnStatusCss = 'status';
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
        Schema::table($this->table, function (Blueprint $table) {
            if (!Schema::hasColumn($this->table, $this->columnStatusCss)) {
                $table->integer('status')->nullable()->after('lang_id');
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
        Schema::table('css', function (Blueprint $table) {
            if (Schema::hasColumn($this->table, $this->columnStatusCss)) {
                $table->dropColumn($this->columnStatusCss);
            }
        });
    }
}
