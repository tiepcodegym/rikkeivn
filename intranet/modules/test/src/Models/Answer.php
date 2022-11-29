<?php

namespace Rikkei\Test\Models;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Test\View\ViewTest;

class Answer extends CoreModel
{
    protected $table = 'ntest_answers';
    protected $fillable = ['label', 'content', 'is_temp'];
   
    public $timestamps = false;
    
    /**
     * get questions that belongs to
     * @return type
     */
    public function questions() {
        return $this->belongsToMany('\Rikkei\Test\Models\Question', 'ntest_question_answer', 'answer_id', 'question_id')
                ->withPivot('is_correct');
    }
    
    public static function validateMessage($qType, $data)
    {
        $errors = [];
        if (in_array($qType, ViewTest::ARR_TYPE_1)) {
            if (strlen($data['content']) < 1) {
                $errors[] = trans('test::test.Answer content is required');
            }
        } elseif (!$data['label'] || strlen($data['content']) < 1) {
            if (!$data['label']) {
                $errors[] = trans('test::test.Answer label is required');
            }
            if (strlen($data['content']) < 1) {
                $errors[] = trans('test::test.Answer content is required');
            }
        }
        return $errors;
    }
    
    public static function validateType2Answer($correctAnswers)
    {
        foreach ($correctAnswers as $ansId) {
            if (!$ansId) {
                return [trans('test::test.Please select correct answer')];
            }
        }
    }
    
    /**
     * delete answer not attach to question
     * @return type
     */
    public static function delNotAttach()
    {
        return self::whereNotIn('id', function ($query) {
           $query->select('answer_id')
                   ->from('ntest_question_answer');
        })->delete();
    }
    
}
