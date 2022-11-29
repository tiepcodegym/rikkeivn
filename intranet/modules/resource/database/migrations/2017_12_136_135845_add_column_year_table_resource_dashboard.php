<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnYearTableResourceDashboard extends Migration
{
    
    protected $tbl = 'resource_dashboard';
    protected $column = 'year';


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
            $table->integer($this->column)->nullable()->after('type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable($this->tbl) && Schema::hasColumn($this->tbl, $this->column)) {
            Schema::table($this->tbl, function (Blueprint $table) {
                $table->dropColumn($this->column);
            });
        }
    }
}
