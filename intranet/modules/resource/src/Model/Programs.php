<?php
namespace Rikkei\Resource\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Core\View\CacheHelper;
use Exception;
use Rikkei\Team\View\Config;
use Illuminate\Database\Eloquent\SoftDeletes;
use Rikkei\Resource\Model\CandidateProgramming;
use Rikkei\Project\Model\ProjectMemberProgramLang;
use Rikkei\Project\Model\ProjectProgramLang;
use Rikkei\Resource\Model\RequestProgramming;
use Rikkei\Core\View\Form;

class Programs extends CoreModel
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $table = 'programming_languages';
    public $timestamps = false;
    const KEY_CACHE = 'programming_languages';
    const KEY_LIST = 'programming_languages_list';
    const KEY_DATAIL = 'detail';
    
    const IS_PRIMARY_CHART = 1;
    const OTHER_CHART_COLOR = "#ff8293";
    
    /**
     *  store this object
     * @var object
     */
    protected static $instance;
    protected $fillable = ['name','primary_chart'];
    /**
     * get list
     * 
     * @return objects
     */
    public function getList()
    {
        if ($programs = CacheHelper::get(self::KEY_CACHE)) {
            return $programs;
        }
        $programs = self::orderBy('name', 'asc')->select('*')->get();
        CacheHelper::put(self::KEY_CACHE, $programs);
        return $programs;
    }
    
    /**
     * Get programs name by programs id
     * @param array $ids array program_id
     * @return array
     */
    public function getNamesByIds($ids) {
        if (!is_array($ids)) $ids = array($ids);
        $pros = self::whereIn('id', $ids)->select('name')->get();
        $result = [];
        foreach ($pros as $pro) {
            $result[] = $pro->name;
        }
        
        return $result;
    }

    /**
     * get instance
     * 
     * @return \self
     */
    public static function getInstance()
    {
        if (! isset(self::$instance)) {
            self::$instance = new static;
        }
        return self::$instance;
    }
    
    /**
     * rewrite save model
     * 
     * @param array $options
     */
    public function save(array $options = array()) {
        try {
            CacheHelper::forget(self::KEY_CACHE);
            return parent::save($options);
        } catch (Exception $ex) {

        }
        
    }
    
    /**
     * get list programs lang format option
     * 
     * @return array
     */
    public static function getListOption()
    {
        if ($result = CacheHelper::get(self::KEY_CACHE)) {
            return $result;
        }
        $result = self::select('id', 'name')
            ->orderBy('name')
            ->lists('name', 'id')
            ->toArray();
        if (!count($result)) {
            return null;
        }
        CacheHelper::put(self::KEY_CACHE, $result);
        return $result;
    }

    /**
     * get collection to show grid data
     * @return collection model
    */
     public static function getGridData() {
        $pager = self::getPagerData();
        $collection = self::select(['*'])->orderBy($pager['order'], $pager['dir']);
        $collection = self::filterGrid($collection);
        $collection = self::pagerCollection($collection, $pager['limit'], $pager['page']);
        return $collection;
    }

    /**
      * get id language by name language
      * @param string $valuelang language name
      * @return id language
    */
    public static function getIdByName($valuePro) {
        $programLang = self::select('name','id')->get()->toArray();
        $arrayId = array();
        foreach ($programLang as $valueProgram) {
            foreach ($valuePro as $namePro) {
                if(strtolower(str_replace(' ', '', $namePro))  
                    == strtolower(str_replace(' ', '', $valueProgram['name'])))
                    array_push($arrayId, $valueProgram['id']);
            }
        }
        if($arrayId) {
            return $arrayId;
        } else {
            return false;
        }
    }

    /**
     * check program language
    */  
    public static function checkPro($id) {
        if(CandidateProgramming::whereIn('programming_id',$id)->count() > 0)
            return false;
        if(ProjectMemberProgramLang::whereIn('prog_lang_id',$id)->count() > 0)
            return false;
        if(ProjectProgramLang::whereIn('prog_lang_id',$id)->count() > 0)
            return false;
        if(RequestProgramming::whereIn('programming_id',$id)->count() > 0)
            return false;
        return true;
    }

    /**
     * check exit program language by name
    */
    public static function checkExist($name, $id = null) {
    if ($id == null) {
        return self::where('name',trim($name))->count();
    }
    return self::where('name',trim($name))->whereNotIn('id',[$id])->count();
    }

    /**
     * get pager data
    */
    public static function getPagerData($urlSubmitFilter = null, array $pagerOption = [])
    {
        $pager = array_merge([
            'limit' => 50,
            'order' => 'id',
            'dir' => 'desc',
            'page' => 1
        ], $pagerOption);
        $pagerFilter = (array) Form::getFilterPagerData(null, $urlSubmitFilter);
        $pagerFilter = array_filter($pagerFilter);
        if ($pagerFilter) {
            $pager = array_merge($pager, $pagerFilter);
        }
        return $pager;
    }
    
    /**
     * Get programs by programs id
     * @param array $ids array program_id
     * @return array
     */
    public static function getProgLangByIds($ids) {
        $result = [];
        if (count($ids) == 1) {
            $pro = self::where('id', $ids)->first();
            $result[$pro->id] = $pro->name;
        } else {
            $pros = self::whereIn('id', $ids)->get();
            foreach ($pros as $pro) {
                $result[$pro->id] = $pro->name;
            }
        }
        return $result;
    }
    
    /**
     * get Name programs by id
     * @param type $id
     */
    public static function getNameById($id)
    {
        $list = self::getListOption();
        return isset($list[$id]) ?  $list[$id] : null;
    }

    /**
     * check existed program by name and employeeId
     * @param string $name
     * @param mixed $employeeId
     * @return array
     */
    public static function checkExistLikeName($name, $employeeId=null)
    {
        $pro = self::select('programming_languages.id');
        if ($employeeId) {
            $pro->join('employee_programs', 'employee_programs.programming_id', '=', 'programming_languages.id')
                    ->where('employee_id', '=', $employeeId);
        }
        $pro = $pro->where('programming_languages.name', 'LIKE', addslashes("%$name%"))->get();
        return array_map(function($item){
            return $item['id'];
        },
        $pro->toArray());
    }
}
