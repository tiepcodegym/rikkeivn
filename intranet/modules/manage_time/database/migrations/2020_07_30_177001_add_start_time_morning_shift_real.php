<?php

use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Rikkei\Resource\View\getOptions;

class AddstartTimeMorningShiftReal extends Migration
{
    private $tbl = 'manage_time_timekeepings';
    private $col1 = 'start_time_morning_shift_real';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    { 
        if (!Schema::hasTable($this->tbl) || Schema::hasColumn($this->tbl, $this->col1)) {
            return;
        }
        Schema::table($this->tbl, function(Blueprint $table) {
            $table->string($this->col1)->nullable()->comment('giờ vào thực tế');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (!Schema::hasTable($this->tbl) || !Schema::hasColumn($this->tbl, $this->col1)) {
            return;
        }
        Schema::table($this->tbl, function (Blueprint $table) {
            $table->dropColumn($this->col1);
        });
    }
}
