<?php

namespace Rikkei\Test\Models;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Test\View\ViewTest;
use Rikkei\Test\Models\LangGroup;
use Rikkei\Test\Models\TestQuestion;

class Question extends CoreModel
{
    protected $table = 'ntest_questions';
    protected $fillable = [
        'row_order',
        'content', 
        'image_urls', 
        'parent_id', 
        'type', 
        'is_temp', 
        'explain', 
        'multi_choice', 
        'status', 
        'is_editor'
    ];
    public $colsSync = [
        'type',
        'status',
    ];

    /**
     * get tests
     * @return type
     */
    public function tests() {
        return $this->belongsToMany('\Rikkei\Test\Models\Test', 'ntest_test_question', 'question_id', 'test_id');
    }
    
    /**
     * get answers
     * @return type
     */
    public function answers() {
        return $this->belongsToMany('\Rikkei\Test\Models\Answer', 'ntest_question_answer', 'question_id', 'answer_id')
                ->withPivot('is_correct');
    }
    
    /**
     * get child questions
     * @return type
     */
    public function childs() {
        return $this->hasMany('\Rikkei\Test\Models\Question', 'parent_id');
    }
    
    /**
     * set image urls serialize
     * @param type $value
     */
    public function setImageUrlsAttribute($value) {
        $this->attributes['image_urls'] = serialize($value);
    }
    
    /**
     * get image urls unserialize
     * @param type $value
     * @return array
     */
    public function getImageUrlsAttribute($value) {
        return unserialize($value);
    }
    
    /**
     * get results
     * @return type
     */
    public function results() {
        return $this->hasMany('\Rikkei\Test\Models\TestResult', 'question_id');
    }
    
    /**
     * filter question content
     * @param type $value
     * @return type
     */
    public function getContentAttribute($value) {
        return str_replace('&nbsp;', ' ', $value);
    }
    
    /*
     * get status label
     */
    public function statusLabel($listStatus = null)
    {
        if (!$listStatus) {
            $listStatus = ViewTest::listStatusLabel();
        }
        if (isset($listStatus[$this->status])) {
            return $listStatus[$this->status];
        }
        return null;
    }
    
    /**
     * get categories that belongs to
     * @return type
     */
    public function categories()
    {
        $builder = $this->belongsToMany('\Rikkei\Test\Models\Category', 'ntest_question_category', 'question_id', 'cat_id');
        return Category::joinLang($builder)->select('id', 'name', 'type_cat');
    }
    
    /**
     * get array cat ids
     * @return type
     */
    public function getArrCatIds()
    {
        $categories = $this->categories;
        if ($categories->isEmpty()) {
            return [];
        }
        $results = [];
        foreach ($categories as $cat) {
            if (isset($results[$cat->type_cat])) {
                $results[$cat->type_cat][$cat->id] = $cat->name;
            } else {
                $results[$cat->type_cat] = [$cat->id => $cat->name];
            }
        }
        return $results;
    }
    
    /*
     * delete question not attach test
     */
    public static function delNotAttach()
    {
        return self::whereNull('parent_id')
                ->whereNotIn('id', function ($query) {
                    $query->select('question_id')
                            ->from('ntest_test_question');
                })->delete();
    }

    /*
     * get parent and child content
     */
    public function mergeChildContent()
    {
        $content = $this->content;
        $childs = $this->childs;
        if ($childs->isEmpty()) {
            return $content;
        }
        foreach ($childs as $cItem) {
            $content .= ' ' . $cItem->content;
        }
        return $content;
    }

    /*
     * get lang code of question
     */
    public function getLangCode($testId = null)
    {
        if (!$testId) {
            return null;
        }
        $testLang = LangGroup::where('test_id', $testId)->first();
        if (!$testLang) {
            return null;
        }
        return $testLang->lang_code;
    }

    /**
     * update other question lang
     *
     * @param int $testId
     * @return void
     */
    public function syncOptionLang($testId, $data = [])
    {
        $testIds = LangGroup::listTestIdsSameGroup($testId, $testId);
        if (!$testIds) {
            return false;
        }
        $questionOrder = TestQuestion::getQuestionOrder($testId, $this->id);
        $questionIds = TestQuestion::select('question_id')
            ->whereIn('test_id', $testIds)
            ->where('order', $questionOrder)
            ->pluck('question_id')
            ->toArray();
        if (!$questionIds) {
            return;
        }
        $collectQuestions = self::whereIn('id', $questionIds);
        $dataUpdate = [];
        foreach ($this->colsSync as $col) {
            $dataUpdate[$col] = $this->{$col};
        }
        $collectQuestions->update($dataUpdate);
        //update category
        $typeCats = isset($data['type_cats']) && $data['type_cats'] ? array_filter($data['type_cats']) : [];
        $collectQuestions = $collectQuestions->get();
        if ($collectQuestions->isEmpty()) {
            return true;
        }
        foreach ($collectQuestions as $langQuestion) {
            $langQuestion->categories()->sync($typeCats);
        }
        if (!$typeCats) {
            return true;
        }
        //update test category
        $testLangs = Test::whereIn('id', $testIds)->get();
        if ($testLangs->isEmpty()) {
            return true;
        }
        foreach ($testLangs as $testLang) {
            $testQCats = $testLang->question_cat_ids;
            if (!$testQCats) {
                $testQCats = [];
            } else {
                $testQCats = unserialize($testQCats);
            }
            foreach ($typeCats as $catId) {
                if (!in_array($catId, $testQCats)) {
                    $testQCats[] = $catId;
                }
            }
            $testLang->update(['question_cat_ids' => $testQCats]);
        }
    }

    /**
     * find question by testId and Order
     *
     * @param int $testId
     * @param int $order
     * @return Object
     */
    public static function findByTestAndOrder($testId, $order)
    {
        return self::whereIn('id', function ($query) use ($testId, $order) {
                $query->select('question_id')
                    ->from(TestQuestion::getTableName())
                    ->where('test_id', $testId)
                    ->where('order', $order);
            })
            ->first();
    }

    /**
     * get list answers group by question_id
     *
     * @param type $questionIds
     * @return type
     */
    public static function getListAnswers($questionIds)
    {
        return Answer::select('answer.id', 'answer.label', 'qans.question_id')
            ->from(Answer::getTableName() . ' as answer')
            ->join('ntest_question_answer as qans', 'qans.answer_id', '=', 'answer.id')
            ->whereIn('qans.question_id', $questionIds)
            ->get()
            ->groupBy('question_id');
    }
}
