<?php

namespace Rikkei\Test\Models;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Core\Model\ArrayPrimaryKeyTrait;

class TestQuestion extends CoreModel
{
    protected $table = 'ntest_test_question';
    protected $primaryKey = ['test_id', 'question_id'];
    protected $fillable = ['test_id', 'question_id', 'order'];
    public $incrementing = false;
    public $timestamps = false;

    use ArrayPrimaryKeyTrait;

    public static function listByTestIds($testIds = [])
    {
        return self::whereIn('test_id', $testIds)
            ->get();
    }

    /**
     * get question order
     * 
     * @param int $testId
     * @param int $questionId
     */
    public static function getQuestionOrder($testId, $questionId, $returnNull = false)
    {
        $questionOrder = TestQuestion::where('test_id', $testId)
            ->where('question_id', $questionId)
            ->first();
        if (!$questionOrder) {
            if ($returnNull) {
                return null;
            }
            return (int) TestQuestion::where('test_id', $testId)->max('order') + 1;
        }
        return $questionOrder->order;
    }

    /**
     * get item by testId and order
     *
     * @param int $testId
     * @param int $order
     * @return Object
     */
    public static function getByTestAndOrder($testId, $order)
    {
        return TestQuestion::where('test_id', $testId)
            ->where('order', $order)
            ->first();
    }

    /**
     * list array order by test id and array question ids
     *
     * @param int $testId
     * @param array $questionIds
     * @return array
     */
    public static function getOrdersByQuestionIds($testId, $questionIds = [])
    {
        return self::where('test_id', $testId)
            ->whereIn('question_id', $questionIds)
            ->pluck('order')
            ->toArray();
    }

    /**
     * check total of questions in other lang equal current test questions
     *
     * @param array $testLangIds
     * @param int $totalQuestion
     * @return boolean
     */
    public static function checkEqualQuestions($testLangIds, $totalQuestion)
    {
        $equalQuestions = true;
        $langQuestions = TestQuestion::listByTestIds($testLangIds)->groupBy('test_id');
        if (!$langQuestions->isEmpty()) {
            foreach ($langQuestions as $lQuestions) {
                if ($totalQuestion != $lQuestions->count()) {
                    $equalQuestions = false;
                    break;
                }
            }
        }
        return $equalQuestions;
    }
}
