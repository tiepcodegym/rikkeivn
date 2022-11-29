<?php

namespace Rikkei\Test\Models;

use Illuminate\Database\Eloquent\Relations;
use Illuminate\Support\Facades\DB;
use Rikkei\Core\Model\CoreModel;
use Rikkei\Test\View\ViewTest;

class WrittenQuestion extends CoreModel
{
    protected $table = 'ntest_written_questions';
    protected $fillable = [
        'status',
        'content',
        'test_id',
    ];

    /**
     * Get answers
     * @return Relations\BelongsToMany
     */
    public function writtenAnswers()
    {
        return $this->belongsToMany('\Rikkei\Test\Models\WrittenQuestionAnswer', 'ntest_written_questions_answers', 'question_id', 'id')
            ->withPivot('is_correct');
    }

    /**
     * Get collect written question by testID
     * @param $testId
     * @param null $status
     * @return mixed
     */
    public static function getCollectWrittenQuestion($testId, $status = null)
    {
        $response = self::select('ntest_written_questions.*', 'ntest_written_category.cat_id')
            ->leftJoin('ntest_written_category', 'ntest_written_category.written_id', '=', 'ntest_written_questions.id')
            ->where('ntest_written_questions.test_id', $testId);
        if (isset($status)) {
            $response->where('status', $status);
        }

        return $response;
    }

    /**
     * List written questions by ids
     * @param $ids
     * @param null $status
     * @return mixed
     */
    public static function listWrittenQuestion($ids, $status = null)
    {
        $ids = !is_array($ids) ? (array)$ids : $ids;
        $response = self::select('ntest_written_questions.id', 'content', 'ntest_category_lang.name as cat_name', 'status')
            ->leftjoin('ntest_written_category', 'ntest_written_category.written_id', '=', 'ntest_written_questions.id')
            ->leftjoin('ntest_category_lang', 'ntest_written_category.cat_id', '=', 'ntest_category_lang.cat_id')
            ->whereIn('ntest_written_questions.id', $ids);
        if (isset($status)) {
            $response->where('ntest_written_questions.status', $status);
        }

        return $response->get();
    }

    /**
     * get Category of written question
     * @param $writtenId
     * @return mixed
     */
    public static function getWrittenCat($writtenId)
    {
        return DB::table('ntest_category_lang')
            ->join('ntest_written_category', 'ntest_written_category.cat_id', '=', 'ntest_category_lang.cat_id')
            ->where('ntest_written_category.written_id', $writtenId)
            ->first();
    }

    /**
     * Get all category of written question By TestID
     * @param $testId
     * @return mixed
     */
    public static function listWrittenCatByTestID($testId)
    {
        return Category::join('ntest_category_lang', 'ntest_category_lang.cat_id', '=', 'ntest_categories.id')
            ->join('ntest_written_category', 'ntest_written_category.cat_id', '=', 'ntest_category_lang.cat_id')
            ->join('ntest_written_questions', 'ntest_written_questions.id', '=', 'ntest_written_category.written_id')
            ->where('ntest_written_questions.test_id', $testId)
            ->groupBy('ntest_categories.id')
            ->get();
    }
}
