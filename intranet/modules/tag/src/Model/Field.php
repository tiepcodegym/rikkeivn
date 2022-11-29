<?php

namespace Rikkei\Tag\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Core\View\CacheHelper;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Rikkei\Tag\View\TagGeneral;

class Field extends CoreModel
{
    use SoftDeletes;
    
    protected $table = 'kl_fields';
    
    const KEY_CACHE_LIST = 'kl_field_list';
    
    /**
     * rewrite save
     * 
     * @param array $options
     */
    public function save(array $options = array())
    {
        // auto render slug
        if (!$this->code) {
            $this->code = Str::slug($this->name, '_');
            // render slug ultil not exits slug
            while(1) {
                $existsSlug = self::withTrashed()
                    ->select(DB::raw('count(*) as count'))
                    ->where('code', $this->code);
                if ($this->id) {
                    $existsSlug->where('id', '!=', $this->id);
                }
                $existsSlug = $existsSlug->first();
                if ($existsSlug && $existsSlug->count) {
                    $this->code = $this->code . substr(md5(mt_rand() . time()), 0, 5);
                } else {
                    break;
                }
            }
        }
        $this->saveBefore();
        try {
            $result = parent::save($options);
            CacheHelper::forget(self::KEY_CACHE_LIST, $this->set);
            TagGeneral::incrementConfigTagVersion();
            return $result;
        } catch (Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * before save: sort_order
     */
    public function saveBefore()
    {
        if ($this->sort_order) {
            return true;
        }
        $sibling = self::select('sort_order')
            ->where('parent_id', $this->parent_id)
            ->orderBy('sort_order', 'desc')
            ->first();
        if (!$sibling) {
            $this->sort_order = 0;
            return true;
        }
        $this->sort_order = $sibling->sort_order + 1;
    }
    
    /**
     * rewrite delete
     *  delete this and all child
     * 
     * @param array $options
     */
    public function delete()
    {
        $ids = TagGeneral::getFieldIdsChildren($this);
        try {
            $result = self::whereIn('id', $ids)->delete();
            CacheHelper::forget(self::KEY_CACHE_LIST, $this->set);
            TagGeneral::incrementConfigTagVersion();
            return $result;
        } catch (Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * get all field path of id root
     * 
     * @param int $idRoot
     * @param array $typesSelect
     * @return array
     */
    public static function getFieldPath($idRoot, $typesSelect = [])
    {
        if ($result = CacheHelper::get(self::KEY_CACHE_LIST, $idRoot)) {
            return $result;
        }
        $collection = self::select(['id', 'parent_id', 'color', 'name',
            'type'])
            ->where('set', $idRoot);
        if ($typesSelect) {
            $collection->whereIn('type', $typesSelect);
        }
        $collection = $collection->orderBy('sort_order', 'asc')
            ->orderBy('name', 'asc')
            ->get();
        if (!count($collection)) {
            return [];
        }
        $result = [];
        self::getFieldPathRecursive($collection, $idRoot, $result);
        CacheHelper::put(self::KEY_CACHE_LIST, $result, $idRoot);
        return $result;
    }
    
    /**
     * call recursive of field path
     * 
     * @param collection $collection
     * @param int $idParentCheck
     * @param array $result
     * @return boolean
     */
    protected static function getFieldPathRecursive(
            &$collection, 
            $idParentCheck, 
            &$result
    ) {
        if (!count($collection)) {
            return true;
        }
        foreach ($collection as $keyIndex => $item) {
            // init element result
            if (!isset($result[$item->id])) {
                $result[$item->id] = [
                    'parent' => [],
                    'child' => [],
                    'data' => []
                ];
            }
            $result[$item->id]['data'] = [
                'name' => $item->name,
                'color' => $item->color,
                'type' => $item->type
            ];
            if ($item->parent_id != $idParentCheck) {
                continue;
            }
            if (!isset($result[$idParentCheck])) {
                $result[$idParentCheck] = [
                    'parent' => [],
                    'child' => [],
                    'data' => []
                ];
            }
            // insert array: parent in db + array parent of parent
            $result[$item->id]['parent'] = 
                array_merge([$idParentCheck], $result[$idParentCheck]['parent']);
            // insert child element
            $result[$idParentCheck]['child'][] = $item->id;
            $collection->forget($keyIndex);
            self::getFieldPathRecursive($collection, $item->id, $result);
        }
    }
    
    /**
     * find item
     * 
     * @param int $id
     * @return model
     */
    public static function getItemResponse($id)
    {
        return self::select(['id', 'name', 'status', 'set', 'type',
            'parent_id', 'sort_order', 'color'])
            ->where('id', $id)
            ->first();
    }
    
    /**
     * check has cache
     * 
     * @param int $idRoot
     * @return boolean
     */
    public static function hasCacheList($idRoot)
    {
        return CacheHelper::has(self::KEY_CACHE_LIST, $idRoot);
    }
    
    /**
     * get all child id of $fieldId
     * @param type $fieldId
     * @return type
     */
    public static function getChildIds ($fieldId) {
        if (!is_array($fieldId)) {
            $fieldId = [$fieldId];
        }
        $subParentIds = self::whereIn('parent_id', $fieldId)
                ->lists('id')->toArray();
        if (!$subParentIds) {
            return $fieldId;
        }
        return array_merge($fieldId, self::getChildIds($subParentIds));
    }
    
    /**
     * get list tag belongs thi fields
     * @param type $tagIds
     * @return type
     */
    public function tags() {
        return $this->hasMany('Rikkei\Tag\Model\Tag', 'field_id');
    }
}
