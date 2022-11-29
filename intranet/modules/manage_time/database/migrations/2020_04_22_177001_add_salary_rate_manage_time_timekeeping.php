<?php

use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Rikkei\Resource\View\getOptions;

class AddSalaryRateManageTimeTimekeeping extends Migration
{
    private $tbl = 'manage_time_timekeepings';
    private $col1 = 'tk_rate_id';

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
        $getTkRate = DB::table('timekeeping_rate')->select('id')->where('rate', 1)->first();
        if (!$getTkRate) {
            $now = Carbon::now();
            DB::table('timekeeping_rate')->insert([
                'rate' => 1,
                'color' => '#57BB8A',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
        Schema::table($this->tbl, function(Blueprint $table) {
            $table->unsignedInteger('tk_rate_id')->default(1)->comment('1: hưởng lương 100 %');
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
