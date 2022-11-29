<?php

namespace Rikkei\Sales\Model;

use Rikkei\Sales\Model\CssCategory;

class CssQuestion extends \Rikkei\Core\Model\CoreModel
{
    
    protected $table = 'css_question';
    protected $fillable = ['content', 'category_id', 'sort_order', 'is_overview_question', 'quest_lang_id', 'explain'];
    public $timestamps = false;


    /**
     * Get overview question by category
     * @param ing $categoryId
     * @param int $isOverviewQuestion
     */
    public function getOverviewQuestionByCategory($categoryId, $isOverviewQuestion = 1, $langId = null){
        $item = self::where("category_id", $categoryId)
                ->where('is_overview_question', $isOverviewQuestion);
        if ($langId !== null) {
            $item->where('quest_lang_id', $langId);
        }
        return $item->first();
    }
    
    /**
     * Get questions list by category
     * @param int $categoryId
     * @return object list questions
     */
    public function getQuestionByCategory($categoryId){
        return self::where('category_id', $categoryId)->get();
    }
}
