<?php

namespace Rikkei\Test\Models;

use Illuminate\Support\Facades\DB;
use Rikkei\Core\Model\CoreModel;

class WrittenQuestionAnswer extends CoreModel
{
    protected $table = 'ntest_written_question_answers';
    protected $fillable = [
        'employee_email',
        'written_id',
        'answer',
        'result_id',
    ];

    public static function saveSubmitWrittenResult($testResult, $writtenAnswers, $resultId)
    {
        if (is_array($writtenAnswers) && !empty($writtenAnswers)) {
            $answers = [];
            foreach ($writtenAnswers as $writtenQuestion => $answer) {
                $answers[] = [
                    'employee_email' => $testResult['employee_email'],
                    'written_id' => $writtenQuestion,
                    'answer' => $answer,
                    'result_id' => $resultId,
                ];
            }
            self::insert($answers);
        }
    }

    /**
     * Đếm số câu đã trả lời theo result_ids
     * @param  array|string $resultIds
     * @return array
     */
    public static function countAnswerByResultIds($resultIds)
    {
        $resultIds = is_array($resultIds) ? $resultIds : (array)$resultIds;
        $sql = '';
        foreach ($resultIds as $result) {
            $sql .= '(SELECT COUNT(id) FROM ntest_written_question_answers WHERE result_id = ' . $result . ') as count_' . $result . ',';
        }
        $sql = trim($sql, ',');

        return self::Select(
            DB::raw($sql)
        )
            ->first();
    }
}
