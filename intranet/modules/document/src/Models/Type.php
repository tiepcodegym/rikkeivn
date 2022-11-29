<?php

namespace Rikkei\Document\Models;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Team\View\Config;
use Rikkei\Core\View\Form;
use Rikkei\Document\View\DocConst;

class Type extends CoreModel
{
    protected $table = 'documenttypes';
    protected $fillable = ['name', 'parent_id', 'status', 'order'];

    /**
     * get list type
     * @return type
     */
    public static function getGridData()
    {
        $pager = Config::getPagerData();
        $collection = self::select('type.id', 'type.name', 'type.parent_id', 'type.status', 'type.order', 'parent.name as parent_name')
                ->from(self::getTableName() . ' as type')
                ->leftJoin(self::getTableName() . ' as parent', 'type.parent_id', '=', 'parent.id')
                ->groupBy('type.id');
        self::filterGrid($collection);
        if (Form::getFilterPagerData('order')) {
            $collection->orderBy($pager['order'], $pager['dir']);
        } else {
            $collection->orderBy('type.created_at', 'desc');
        }
        self::pagerCollection($collection, $pager['limit'], $pager['page']);
        return $collection;
    }

    /**
     * get label status
     * @param type $labels
     * @return type
     */
    public function getLabelStatus($labels = [])
    {
        if (!$labels) {
            $labels = DocConst::listTypeStatuses();
        }
        if (isset($labels[$this->status])) {
            return $labels[$this->status];
        }
        return null;
    }

    /**
     * get list types
     * @return collection
     */
    public static function getList($exclude = [], $hasParent = false, $isEnable = false)
    {
        $result = self::select('id', 'name', 'parent_id');
        if ($exclude) {
            $result->whereNotIn('id', self::allIds($exclude));
        }
        if ($hasParent) {
            $result->whereNotNull('parent_id');
        }
        if ($isEnable) {
            $result->where('status', DocConst::STT_ENABLE)
                    ->orderBy('order', 'asc');
        }
        return $result->get();
    }

    /**
     * get all child ids
     * @param type $id
     * @return type
     */
    public static function allIds($ids)
    {
        if (!is_array($ids)) {
            $ids = [$ids];
        }
        $currIds = self::whereIn('parent_id', $ids)->lists('id')->toArray();
        if (!$currIds) {
            return $ids;
        }
        return array_unique(array_merge($ids, self::allIds($currIds)));
    }

    /**
     * insert or update data
     * @param type $data
     * @return boolean
     */
    public static function insertOrUpdate($data = [])
    {
        $item = null;
        if (isset($data['id'])) {
            $item = self::find($data['id']);
            if (!$item) {
                return false;
            }
        }
        if (!isset($data['parent_id']) || !$data['parent_id']) {
            $data['parent_id'] = null;
        }
        if ($item) {
            $item->update($data);
        } else {
            $item = self::create($data);
        }
        //update children status
        if ($item->status == DocConst::STT_DISABLE) {
            $allIds = self::allIds($item->id);
            unset($allIds[array_search($item->id, $allIds)]);
            if ($allIds) {
                self::whereIn('id', $allIds)
                        ->update(['parent_id' => null]);
            }
        }
        return $item;
    }

    /**
     * get view link
     * @return type
     */
    public function getDocViewLink()
    {
        return route('doc::type.view', ['id' => $this->id, 'slug' => str_slug($this->name)]);
    }
}
