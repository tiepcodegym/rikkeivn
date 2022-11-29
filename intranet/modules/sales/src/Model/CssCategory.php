<?php

namespace Rikkei\Sales\Model;

use Rikkei\Sales\Model\CssQuestion;
use Rikkei\Sales\Model\Css;

class CssCategory extends \Rikkei\Core\Model\CoreModel
{
    
    protected $table = 'css_category';
    protected $fillable = ['name', 'parent_id', 'project_type_id', 'css_id', 'code', 'question_explanation', 'sort_order', 'lang_id', 'created_at', 'updated_at'];
    public $timestamps = false;


    /**
     * Get root category by project type
     * @param int $projectTypeId
     * @return object category
     */
    public function getRootCategory($projectTypeId){
        /*if ($projectTypeId == Project::TYPE_ONSITE) {
            $projectTypeId = Project::TYPE_OSDC;
        }*/
        return self::where("parent_id",0)->where('project_type_id',$projectTypeId)->first();
    }

    public function getRootCategoryV2($projectTypeId, $cssId, $code, $createdAt, $lang){
        $collection = self::where("parent_id",0)->where('css_id',$cssId)->where('code', $code)->first();
        if (!$collection) {
            if ($createdAt < Css::CSS_TIME) {
                return self::where("parent_id",0)->where('project_type_id',$projectTypeId)->first();
            } else {
                // $lang = ($lang == Css::VIE_LANG) ? Css::ENG_LANG : $lang;
                $collection = self::where("parent_id",0)->where('css_id', 0)->where('code', 1)->where('lang_id', $lang)->first();
            }
        }
        return $collection;
    }
    
    /**
     * Get category by parent
     * @param int $parentId
     * @return object list category
     */
    public function getCategoryByParent($parentId ,$lang_id = null, $created_at, $project_type_id){
        if ($created_at < Css::CSS_TIME) {
            if($lang_id == null) {
                return self::where('parent_id', $parentId)
                    ->where('lang_id',Css::JAP_LANG)
                    ->get();
            } else {
                if ($project_type_id != Css::TYPE_ONSITE && $lang_id == Css::VIE_LANG) {
                    $lang_id = 1;
                }
                return self::where('parent_id',$parentId)
                        ->where('lang_id',$lang_id)->get();
            }
        } else {
            return self::where('parent_id',$parentId)->get();
        }
    }
    
    /**
     * Get category by question
     * @param int $questionId
     */
    public function getCateByQuestion($questionId){
        return self::where('id', function($query) use ($questionId){
                    $query->select('category_id')
                    ->from(with(new CssQuestion)->getTable())
                    ->where('id', $questionId);
                })->first();
    }

    /**
     * get id category by name
     * @param name cate
     * @return id cate
    */    
    public static function getIdCateByName($nameCate) {
        $idCate = self::where('name',trim($nameCate))->select('id')->first();
        if($idCate) {
            return $idCate->id;
        }
        return null;
    }
}
