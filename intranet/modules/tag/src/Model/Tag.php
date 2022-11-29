<?php

namespace Rikkei\Tag\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Tag\View\TagConst;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Rikkei\Core\View\CoreQB;
use Rikkei\Tag\View\TagGeneral;
use Exception;
use Rikkei\Team\View\Config;

class Tag extends CoreModel
{
    use SoftDeletes;
    
    protected $table = 'kl_tags';
    
    /**
     * get tags of a field
     * 
     * @param model $field
     * @return boolean|array
     */
    public static function getTagsOfField($field)
    {
        if (!in_array($field->type, [
            TagConst::FIELD_TYPE_TAG
        ])) {
            return false;
        }
        return self::select('id', 'value', 'status')
            ->where('field_id', $field->id)
            ->orderBy('updated_at', 'desc')
            ->orderBy('sort_order', 'asc')
            ->orderBy('value', 'asc')
            ->get()
            ->toArray();
    }
    
    /**
     * create tag for a field
     * 
     * @param int $fieldId
     * @param string $tagName
     * @return boolean
     */
    public static function addTagForField($fieldId, $tagName)
    {
        $tagDuplicate = self::where('field_id', $fieldId)
            ->where('value', $tagName)
            ->count();
        if ($tagDuplicate) {
            return false;
        }
        $item = new self();
        $item->setData([
            'field_id' => $fieldId,
            'value' => $tagName,
            'status' => TagConst::TAG_STATUS_APPROVE
        ])->save();
        return $item;
    }
    
    /**
     * create tag for a field
     * 
     * @param object $tag
     * @param string $tagName
     * @return boolean
     */
    public static function saveTag($tag, $tagName)
    {
        $tagDuplicate = self::where('field_id', $tag->field_id)
            ->where('id', '<>', $tag->id)
            ->where('value', $tagName)
            ->count();
        if ($tagDuplicate) {
            return false;
        }
        $tag->value = $tagName;
        $tag->save();
        return $tag;
    }
    
    /**
     * create of find tag
     * 
     * @param type $fieldId
     * @param type $tagName
     * @param type $status
     * @return \self
     */
    public static function createOrFindTag($fieldId, $tagName, $status = null)
    {
        if (!is_array($fieldId)) {
            $fieldId = [$fieldId];
        }
        $itemExists = self::whereIn('field_id', $fieldId)
            ->where('value', $tagName)
            ->first();
        if ($itemExists) {
            return $itemExists;
        }
        $item = new self();
        if (!$status) {
            $status = TagConst::TAG_STATUS_REVIEW;
        }
        $item->setData([
            'field_id' => $fieldId[0],
            'value' => $tagName,
            'status' => $status
        ])->save();
        return $item;
    }
    
    /**
     * approve tag
     * 
     * @return boolean
     */
    public function approveTag()
    {
        if ($this->status == TagConst::TAG_STATUS_APPROVE) {
            return true;
        }
        $this->status = TagConst::TAG_STATUS_APPROVE;
        return $this->save();
    }
    
    /**
     * search tags
     * @param type $fieldIds
     * @param type $key
     * @return type
     */
    public static function searchTags ($fieldIds, $key = null, $data = []) {
        if (!is_array($fieldIds)) {
            $fieldIds = [$fieldIds];
        }
        $result = self::whereIn('field_id', $fieldIds)
                ->where('status', '!=', TagConst::TAG_STATUS_DRAFT);
        if ($key) {
            $result->where('value', 'like', $key .'%');
        }
        if (isset($data['excerpt']) && $data['excerpt']) {
            $result->whereNotIn('id', $data['excerpt']);
        }
        return $result->select('value')->get();
    }
    
    /**
     * count tag review of fields
     * 
     * @param array $fieldIds
     * @return collection
     */
    public static function countTagReviewOfFields($fieldIds)
    {
        $tableTag = self::getTableName();
        $tableField = Field::getTableName();
        $bindingString = implode(',',array_fill(0, count($fieldIds), '?'));
        
        $query = CoreQB::resetQuery();
        $query = 'SELECT t_tag.field_id, SUM(1) AS total_tag, '
            . 'SUM(case when t_tag.status IN (' . TagConst::TAG_STATUS_REVIEW 
                .', ' . TagConst::TAG_STATUS_DRAFT . ') then 1 else 0 end) AS total_tag_review '
            . 'FROM ' . $tableTag . ' AS t_tag '
            . 'JOIN ' . $tableField . ' AS t_field ON t_tag.field_id = t_field.id '
            . 'AND t_field.deleted_at is null '
            . 'WHERE t_tag.field_id IN ('.$bindingString.') '
            . 'AND t_tag.deleted_at is null '
            . 'GROUP BY t_tag.field_id';
        return DB::select($query, $fieldIds);
    }
    
    /**
     * search tag more of a field
     * 
     * @param int $fieldId
     * @param string $searchParam
     * @param array $tagIdsExists
     * @return array
     */
    public static function getMoreTagOfField(
        $fieldId, 
        $searchParam = '',
        $tagIdsExists= []
    ) {
        $config = [
            'page' => 1,
            'limit' => 10
        ];
        $collection = self::select(['id', 'value'])
            ->where('field_id', $fieldId)
            ->where('value', 'like', $searchParam . '%');
        if (count($tagIdsExists)) {
            $collection->whereNotIn('id', $tagIdsExists);
        }
        self::pagerCollection($collection, $config['limit'], $config['page']);
        $result = [
            'total_count' => $collection->total(),
            'incomplete_results' => true,
            'items' => []
        ];
        foreach ($collection as $item) {
            $result['items'][] = [
                'id' => $item->id,
                'text' => $item->value
            ];
        }
        return $result;
    }
    
    /**
     * get tag by ids with field color
     * @param type $ids
     * @return type
     */
    public static function getWithFieldByIds($ids) {
        $tblTag = self::getTableName();
        return self::join(Field::getTableName().' as field', $tblTag.'.field_id', '=', 'field.id')
                ->whereIn($tblTag.'.id', $ids)
                ->select($tblTag.'.id', $tblTag.'.value', 
                        DB::raw('IFNULL(field.color, "'. TagConst::COLOR_DEFAULT .'") as color'))
                ->get();
    }
    
    /**
     * get search tag
     * 
     * @param string $search
     * @param array exists
     * @return collection
     */
    public static function geSearchTag($search, array $exists = [])
    {
        $tableTag = self::getTableName();
        $tableField = Field::getTableName();
        
        return self::select($tableTag.'.id', $tableTag.'.value')
            ->join($tableField.' AS t_field', $tableTag.'.field_id', '=', 't_field.id')
            ->whereNull('t_field.deleted_at')
            ->where('t_field.set', TagConst::SET_TAG_PROJECT)
            ->where($tableTag.'.value', 'like', $search.'%')
            ->whereNotIn($tableTag.'.value', $exists)
            ->limit(10)
            ->get();
    }
    
    /**
     * rewrite save function model
     * 
     * @param array $options
     */
    public function save(array $options = array()) {
        try {
            TagGeneral::incrementLDBVersion();
            TagGeneral::incrementConfigTagVersion();
            $this->value = trim($this->value);
            return parent::save($options);
        } catch (Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * rewrite delte function model
     * 
     * @return type
     * @throws Exception
     */
    public function delete() {
        try {
            TagGeneral::incrementLDBVersion();
            TagGeneral::incrementConfigTagVersion();
            return parent::delete();
        } catch (Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * change data tag alias for tag tag review
     * 
     * @param type $tagOrgId
     * @param type $tagAliasId
     * @return boolean
     */
    public static function tagAlias($tagOrgId, $tagAliasId)
    {
        $tagOrg = self::find($tagOrgId);
        $tagAs = self::find($tagAliasId);
        if (!$tagOrg ||  !$tagAs) {
            return false;
        }
        // check tag org same tag alias data
        $collection = TagValue::select(['field_id', 'tag_id', 'entity_id'])
            ->whereIn('tag_id', [$tagOrgId, $tagAliasId])
            ->get();
        if (!count($collection)) {
            $tagOrg->delete();
            return true;
        }
        $dataTagCheck = [
            'org' => [],
            'as' => []
        ];
        foreach ($collection as $item) {
            if ($item->tag_id == $tagOrgId) {
                $dataTagCheck['org'][] = $tagAliasId . '-' . $item->entity_id 
                    . '-' . $item->field_id;
            } else {
                $dataTagCheck['as'][] = $item->tag_id . '-' . $item->entity_id 
                    . '-' . $item->field_id;
            }
        }
        if (!count($dataTagCheck['org'])) {
            $tagOrg->delete();
            return true;
        }
        $delete = [];
        foreach ($dataTagCheck['org'] as $item) {
            if (in_array($item, $dataTagCheck['as'])) {
                $itemExplode = explode('-', $item);
                $delete[] = [
                    't' => $itemExplode[0],
                    'e' => $itemExplode[1],
                    'f' => $itemExplode[2],
                ];
            }
        }
        DB::beginTransaction();
        try {
            self::deleteDuplicateAs($delete);
            TagValue::where('tag_id', $tagOrgId)
                ->update([
                    'tag_id' => $tagAliasId
                ]);
            $tagOrg->delete();
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }
    
    /**
     * delete duplicate tag org if change it to alias data
     * 
     * @param array $data
     * @return boolean
     */
    protected static function deleteDuplicateAs($data = [])
    {
        if (!count($data)) {
            return true;
        }
        $tableTagValue = TagValue::getTableName();
        $query = 'DELETE FROM `' . $tableTagValue . '` WHERE ';
        foreach ($data as $item) {
            $query .= '(`field_id` = "'.$item['f'].'" AND '
                . '`tag_id` = "'.$item['t'].'" AND '
                . '`entity_id` = "'.$item['e'].'") OR ';
        }
        $query = substr($query, 0, -4);
        return DB::delete($query);
    }

    /**
     * search all tag follow field code
     *
     * @return string
     */
    public static function searchTagFollowFieldCodeSelect2($search, $fieldCode = null)
    {
        $tblTags = self::getTableName();
        $tblField = Field::getTableName();
        $pager = Config::getPagerDataQuery([
            'limit' => 10,
        ]);
        $collection = self::select($tblTags . '.value as text', $tblTags . '.id')
            ->where($tblTags . '.value', 'like', "%{$search}%")
            ->where($tblTags . '.status', TagConst::TAG_STATUS_APPROVE)
            ->orderBy('text', 'ASC');
        if ($fieldCode) {
            $collection->join($tblField . ' AS t_field', 't_field.id', '=', $tblTags . '.field_id')
                ->whereNull('t_field.deleted_at');
            if ($fieldCode === 'other_proj') {
                $collection->whereNotIn('t_field.code', ['language', 'os', 'database']);
            } else {
                $fieldCode = preg_split('/\-/', $fieldCode);
                $collection->whereIn('t_field.code', $fieldCode);
            }
        }
        return self::pagerCollection($collection, $pager['limit'], $pager['page']);
    }

    /**
     * get tag name by tag ids
     *
     * @param array $tagIds
     * @return array
     */
    public static function getTagName(array $tagIds)
    {
        $collection = self::select(['id', 'value'])
            ->whereIn('id', $tagIds)
            ->get();
        if (!count($collection)) {
            return [];
        }
        $result = [];
        foreach ($collection as $item) {
            $result[$item->id] = [
                'id' => $item->id,
                'text' => $item->value
            ];
        }
        return $result;
    }

    /**
     * get tag data of project | language, db, os
     *
     * @param array $tagIds
     * @return array
     */
    public static function getTagDataProj($tagIds = [])
    {
        $fields = Field::select(['id', 'code'])
            ->whereIn('code', ['language', 'os', 'database', 'framework', 'ide', 'english'])
            ->get();
        if (!count($fields)) {
            return [];
        }
        $fieldIds = [];
        $fieldData = [];
        foreach ($fields as $item) {
            $fieldIds[] = $item->id;
            $fieldData[$item->id] = $item->code;
        }
        $collection = self::select(['id', 'value', 'field_id'])
            ->whereIn('field_id', $fieldIds)
            ->where('status', TagConst::TAG_STATUS_APPROVE);
        if ($tagIds) {
            $collection->whereIn('id', $tagIds);
        }
        $collection = $collection->get();
        if (!count($collection)) {
            return [];
        }
        $result = [];
        foreach ($collection as $item) {
            if (isset($fieldData[$item->field_id])) {
                $key = $fieldData[$item->field_id];
                //custom dev environment
                if (in_array($key, ['os', 'database', 'framework', 'ide'])) {
                    $result['dev_env'][$item->id] = $item->value;
                }
                if (in_array($key, ['language', 'database'])) {
                    $result['lang'][$item->id] = $item->value;
                } elseif (in_array($key, ['framework', 'ide'])) {
                    $result['frame'][$item->id] = $item->value;
                    continue;
                } else {
                    // nothing
                }
                $result[$key][$item->id] = $item->value;
            }
        }
        return $result;
    }

    /**
     * get tag data of skills
     *
     * @return collection
     */
    public static function getTagDataSkills()
    {
        $fields = Field::select(['id', 'code'])
            ->whereIn('code', ['language', 'os', 'database'])
            ->get();
        if (!count($fields)) {
            return [];
        }
        $fieldIds = [];
        foreach ($fields as $item) {
            $fieldIds[] = $item->id;
        }
        $collection = self::select(['id', 'value'])
            ->whereIn('field_id', $fieldIds)
            ->where('status', TagConst::TAG_STATUS_APPROVE)
            ->get();
        return $collection;
    }

    /**
     * get tags by code
     * @param array $codes array code of field
     * @return array
     */
    public static function getAllTagByCodes($codes = [])
    {
        $tagTbl = self::getTableName();
        return self::join(Field::getTableName() . ' as field', function ($join) use ($tagTbl) {
            $join->on('field.id', '=', $tagTbl.'.field_id')
                    ->whereNull('field.deleted_at');
        })
            ->whereIn('field.code', $codes)
            ->where($tagTbl.'.status', TagConst::TAG_STATUS_APPROVE)
            ->groupBy($tagTbl.'.id')
            ->orderBy($tagTbl.'.value', 'asc')
            ->lists($tagTbl.'.value', $tagTbl.'.id')
            ->toArray();
    }

    public static function listTagsByIds($ids = [])
    {
        return self::whereIn('id', $ids)
                ->lists('value', 'id')
                ->toArray();
    }
}
