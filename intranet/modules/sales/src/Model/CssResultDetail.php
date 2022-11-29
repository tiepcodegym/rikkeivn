<?php

namespace Rikkei\Sales\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Rikkei\Core\Model\CoreModel;

class CssResultDetail extends CoreModel
{
    protected $table = 'css_result_detail';
    
    use SoftDeletes;
    
    public $timestamps = false;
    
    /**
     * Insert into table css_result_detail
     * @param array $data
     */
    public function insertCssResultDetail($cssResultId, $arrQuestion){
        try {
            if (count($arrQuestion)) {
                $countQuestion = count($arrQuestion); 
                for($i=0; $i<$countQuestion; $i++){
                    $detail = new CssResultDetail();
                    $detail->css_result_id = $cssResultId;
                    $detail->question_id = $arrQuestion[$i][0];
                    $detail->point = $arrQuestion[$i][1];
                    $detail->comment = $arrQuestion[$i][2];
                    
                    $detail->save();
                 }
            }
        } catch (Exception $ex) {
            throw $ex;
        }
        
    }
    
    /**
     * Get css result detail by css result
     * @param int $cssResultId
     */
    public function getResultDetailByCssResult($cssResultId){
        return self::where('css_result_id', $cssResultId)->get();
    }
    
    /**
     * Get a row of result detail
     * @param int $resultId
     * @param int $questionId
     */
    public function getResultDetailRow($resultId, $questionId){
        return self::where(['css_result_id' => $resultId, 'question_id' => $questionId])->first();
    }
    
    /**
     * Get row of overview question
     * @param int $resultId
     * @param int $rootCategoryId
     */
    public function getResultDetailRowOfOverview($resultId, $rootCategoryId){
        return self::join('css_question', 'css_question.id', '=', 'css_result_detail.question_id')
                    ->where('css_result_detail.css_result_id',$resultId)
                    ->where('css_question.is_overview_question',1)
                    ->where('css_question.category_id',$rootCategoryId)
                    ->first();
    }

    /**
     * update data css_result_detail
     *
     * @param $cssResultId
     * @param $questionId
     * @param $analysisContent
     *
     * @return void
     */
    public function updateCssResultDetail($cssResultId, $questionId, $analysisContent){
        self::where('css_result_id', '=', $cssResultId)
            ->where('question_id', '=', $questionId)
                ->update(['analysis' => $analysisContent]);
        
    }
}
