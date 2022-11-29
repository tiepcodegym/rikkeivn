<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AlterTableTestQuestionAddUniqueIndexOrder extends Migration
{
    protected $tbl = 'ntest_test_question';

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

        if (Schema::hasColumn($this->tbl, 'order')) {
            Schema::table($this->tbl, function (Blueprint $table) {
                $table->smallInteger('order')->default(0)->change();
            });
        }

        $moreQuestionOrders = DB::table($this->tbl)
            ->select('test_id', 'order', DB::raw('GROUP_CONCAT(question_id) as question_ids'))
            ->havingRaw('COUNT(question_id) > 1')
            ->groupBy('test_id', 'order')
            ->get();

        if (count($moreQuestionOrders) > 0) {
            DB::beginTransaction();
            try {
                foreach ($moreQuestionOrders as $testQuestion) {
                    $aryQuesIds = explode(',', $testQuestion->question_ids);
                    $testId = $testQuestion->test_id;
                    unset($aryQuesIds[0]);
                    $maxOrder = (int) DB::table($this->tbl)
                        ->where('test_id', $testId)
                        ->max('order');
                    foreach ($aryQuesIds as $questionId) {
                        $maxOrder++;
                        DB::table($this->tbl)
                            ->where('test_id', $testId)
                            ->where('question_id', $questionId)
                            ->update(['order' => $maxOrder]);
                    }
                }
                DB::commit();
            } catch (\Exception $ex) {
                DB::rollback();
                throw $ex;
            }
        }

        Schema::table($this->tbl, function (Blueprint $table) {
            $table->unique(['test_id', 'order']);
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
