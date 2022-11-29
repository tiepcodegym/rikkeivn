<?php

namespace Rikkei\Test\Models;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Team\View\Config;
use Rikkei\Core\View\Form;
use Illuminate\Support\Facades\DB;
use Rikkei\Core\View\CacheHelper;
use Illuminate\Support\Facades\Session;
use Rikkei\Core\View\CoreLang;
use Rikkei\Test\Models\TypeMeta;

class Type extends CoreModel
{
    protected $table = 'ntest_types';
    protected $tblLang = 'ntest_types_meta';
    protected $fillable = ['name', 'code', 'parent_id'];

    const GMAT_CODE = 'gmat';

    /**
     * get tests
     * @return collection
     */
    public function tests() {
        return $this->hasMany('\Rikkei\Test\Models\Test', 'type_id', 'id');
    }

    /**
     * join language
     *
     * @param Eloquent Builder $builder
     * @param string $langCode
     */
    public static function joinLang($builder = null, $langCode = null, $tblTypeAs = '')
    {
        $instance = new static;
        if (!$tblTypeAs) {
            $tblTypeAs = $instance->table;
        }
        $query = $builder === null ? $instance->newQuery() : $builder;
        $query->join($instance->tblLang, $instance->tblLang . '.type_id', '=', $tblTypeAs . '.id');
        if ($langCode) {
            $query->leftJoin($instance->tblLang . ' as lang', function ($join) use ($tblTypeAs, $langCode) {
                $join->on('lang.type_id', '=', $tblTypeAs . '.id')
                    ->where('lang.lang_code', '=', $langCode);
            });
        }
        return $query;
    }

    public static function addSelectName(&$query)
    {
        $query->addSelect(
            DB::raw('(CASE WHEN (lang.type_id IS NOT NULL AND lang.name != "") '
                    . 'THEN lang.name ELSE MAX(ntest_types_meta.name) END) as name')
        );
    }

    /**
     * get all data
     * @return collection
     */
    public static function getGridData()
    {
        $tblType = self::getTableName();
        $tblTest = Test::getTableName();
        $langCode = Session::get('locale');
        $pager = Config::getPagerData();
        $collection = self::joinLang(null, $langCode, 'type')
                ->from($tblType . ' as type')
                ->select(
                    'type.id',
                    'type.created_at',
                    'type.parent_id',
                    DB::raw('COUNT(DISTINCT(test.id)) as count_test')
                )
                ->leftJoin($tblTest . ' as test', 'test.type_id', '=', 'type.id')
                ->groupBy('type.id');
        self::addSelectName($collection);
        self::filterGrid($collection);
        
        if (Form::getFilterPagerData('order')) {
            $collection->orderBy($pager['order'], $pager['dir']);
        } else {
            $collection->orderBy('created_at', 'desc');
        }
        self::pagerCollection($collection, $pager['limit'], $pager['page']);
        return $collection;
    }
    
    /**
     * get list types
     * @return collection
     */
    public static function getList($exclude = [], $hasParent = false)
    {
        $langCode = Session::get('locale');
        $result = self::select('type.id', 'type.parent_id', 'type.code')
            ->from(self::getTableName() . ' as type')
            ->groupBy('type.id');
        self::joinLang($result, $langCode, 'type');
        self::addSelectName($result);

        if ($exclude) {
            $result->whereNotIn('type.id', self::allIds($exclude));
        }
        if ($hasParent) {
            $result->whereNotNull('parent_id');
        }
        $result->orderBy('name', 'asc');
        return $result->get();
    }

    public static function getListTypeName()
    {
        $langCode = Session::get('locale');
        $allLang = CoreLang::changeOrder($langCode);
        $result = self::orderBy('name', 'asc');
        $addSelect = [];
        foreach ($allLang as $langKey => $langText) {
            $result->leftJoin("ntest_types_meta as nt_{$langKey}", function($join) use ($langKey) {
                $join->on("nt_{$langKey}.type_id", '=', 'ntest_types.id');
                $join->where("nt_{$langKey}.lang_code", '=', "{$langKey}");
            });
            $addSelect[] = "nt_{$langKey}.name";
        }
        $strAddSelect = implode(',', $addSelect);
        $result->select(DB::raw("coalesce({$strAddSelect}) as name"));
        return $result->get();
    }

    /**
     * get all child ids
     * @param type $id
     * @return type
     */
    public static function allIds($id)
    {
        if (!is_array($id)) {
            $ids = [$id];
        } else {
            $ids = $id;
        }
        return array_merge($ids, 
                self::whereIn('parent_id', $ids)->lists('id')->toArray());
    }
    
    /**
     * get group type (parent is null)
     * @param type $exclude
     * @return type
     */
    public static function getGroupType($exclude = null)
    {
        $langCode = Session::get('locale');
        $results = self::select('type.id', 'type.code', 'type.parent_id')
                ->from(self::getTableName() . ' as type')
                ->whereNull('parent_id')
                ->groupBy('type.id');
        self::joinLang($results, $langCode, 'type');
        self::addSelectName($results);
        if ($exclude) {
            $results->where('type.id', '!=', $exclude);
        }
        return $results->get();
    }
    
    /**
     * get child type
     * @return type
     */
    public function childs()
    {
        $childs = $this->hasMany('\Rikkei\Test\Models\Type', 'parent_id', 'id');
        self::joinLang($childs, Session::get('locale'))
                ->select($this->table . '.id', $this->table.'.code', $this->table . '.parent_id')
                ->groupBy($this->table . '.id');
        self::addSelectName($childs);
        return $childs;
    }
    
    /**
     * to nested items
     * @param type $collection
     * @return type
     */
    public static function toNested($collection)
    {
        $parents = $collection->where('parent_id', null);
        if ($parents->isEmpty()) {
            return $collection;
        }
        $results = [];
        foreach ($parents as $pItem) {
            $arrChild = [];
            $testCount = $pItem->count_test;
            foreach ($collection as $key => $cItem) {
                if ($cItem->parent_id == $pItem->id) {
                    array_push($arrChild, $cItem);
                    $testCount += intval($cItem->count_test);
                    unset($collection[$key]);
                }
            }
            $pItem->count_test = $testCount;
            array_push($results, $pItem);
            $results = array_merge($results, $arrChild);
        }
        return $results;
    }
    
    /**
     * to nested options
     * @param type $collection
     * @param type $selected
     * @param type $parent
     * @param type $depth
     * @return string
     */
    public static function toNestedOptions($collection, $selected = null, $parent = null, $depth = 0)
    {
        if ($collection->isEmpty()) {
            return '';
        }
        $html = '';
        $indent = str_repeat('-- ', $depth);
        if (!$selected) {
            $selected = [];
        }
        if (!is_array($selected)) {
            $selected = [$selected];
        }
        foreach ($collection as $item) {
            if ($item->parent_id == $parent) {
                $html .= '<option data-code="'. $item->code .'" value="'. $item->id .'" '. (in_array($item->id, $selected) ? 'selected' : '') .'>'
                        . $indent . htmlentities($item->name) . '</option>';
                $html .= self::toNestedOptions($collection, $selected, $item->id, $depth + 1);
            }
        }
        return $html;
    }
    
    /**
     * before save
     * @param array $options
     */
    public function save(array $options = array()) {
        CacheHelper::forget('key_test_gmat_type');
        parent::save($options);
    }
    
    /**
     * remove parent
     * @param type $parentId
     * @return type
     */
    public static function removeParent($parentId)
    {
        return self::where('parent_id', $parentId)
                ->update(['parent_id' => null]);
    }

    /**
     * Get list name of test by list id
     * @param array $ids
     * @return array
     */
    public static function getListNameByIds($ids)
    {
        $langCode = Session::get('locale');
        $allLang = CoreLang::changeOrder($langCode);
        $results = self::whereIn('ntest_types.id', $ids);
        $addSelect = [];
        foreach ($allLang as $langKey => $langText) {
            $results->leftJoin("ntest_types_meta as nt_{$langKey}", function($join) use ($langKey) {
                $join->on("nt_{$langKey}.type_id", '=', 'ntest_types.id');
                $join->where("nt_{$langKey}.lang_code", '=', "{$langKey}");
            });
            $addSelect[] = "nt_{$langKey}.name";
        }
        $strAddSelect = implode(',', $addSelect);
        $results->select(DB::raw("coalesce({$strAddSelect}) as name"));
        return $results->lists('name')->toArray();
    }

    public static function saveType($data, $typeId = null)
    {
        DB::beginTransaction();
        try {
            if ($typeId) {
                $type = Type::findOrFail($typeId);
                $parentId = $data['parent_id'];
                $type->parent_id = null;
                if ($parentId) {
                    //set child null parent
                    $type->parent_id = $parentId;
                    Type::removeParent($type->id);
                }
            } else {
                $type = new Type();
                if (isset($data['parent_id'])) {
                    $type->parent_id = $data['parent_id'];
                }
            }
            $type->code = static::setCode($data);
            $type->save();
            $allLang = CoreLang::allLang();
            if ($typeId) {
                $dataMeta = [];
                foreach ($allLang as $langKey => $langVal) {
                    $typeMeta = TypeMeta::getByTypeId($typeId, $langKey);
                    if ($typeMeta) {
                        $typeMeta->name = $data['name_' . $langKey];
                        $typeMeta->save();
                    } else {
                        if (!empty($data['name_' . $langKey])) {
                            $dataMeta[] = [
                                'type_id' => $type->id,
                                'name' => $data['name_' . $langKey],
                                'lang_code' => $langKey,
                            ];
                        }
                    }
                }
                if (count($dataMeta)) {
                    TypeMeta::insert($dataMeta);
                }    
            } else {
                $dataMeta = [];
                foreach ($allLang as $langKey => $langVal) {
                    if (!empty($data['name_' . $langKey])) {
                        $dataMeta[] = [
                            'type_id' => $type->id,
                            'name' => $data['name_' . $langKey],
                            'lang_code' => $langKey,
                        ];
                    }
                }
                if (count($dataMeta)) {
                    TypeMeta::insert($dataMeta);
                }
            }
            DB::commit();
            return $type;
        } catch (Exception $ex) {
            DB::rollback();
        }
    }

    public static function setCode($data)
    {
        $allLang = CoreLang::allLang();
        foreach ($allLang as $langKey => $langVal) {
            if (!empty($data['name_' . $langKey])) {
                return str_slug($data['name_' . $langKey]);
            }
        }
        return null;
    }

    public static function displayName($name) {
        $names = explode(',,', $name);
        $string = '';
        foreach ($names as $line) {
            if ($string === '') {
                $string = $line;
            } else {
                $string .= '<br>' . $line;
            }
        }
        return $string;
    }
}
