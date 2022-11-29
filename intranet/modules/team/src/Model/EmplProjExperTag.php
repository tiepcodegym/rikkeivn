<?php
namespace Rikkei\Team\Model;

use Rikkei\Core\Model\CoreModel;
use Exception;
use Illuminate\Support\Facades\DB;
use Rikkei\Tag\Model\Tag;
use Illuminate\Database\Eloquent\Builder;

class EmplProjExperTag extends CoreModel
{
    protected $table = 'empl_proj_exper_tags';
    protected $primaryKey = ['proj_exper_id', 'tag_id'];
    public $incrementing = false;

    const UPDATED_AT = null;

    /**
     * insert lang of project experience of employee
     *
     * @param int $projExperId
     * @param array $langIds
     * @return boolean
     * @throws Exception
     */
    public static function saveProjExperLang($projExperId, $langIds = [])
    {
        DB::beginTransaction();
        try {
            self::where('proj_exper_id', $projExperId)
                ->delete();
            if (!$langIds) {
                DB::commit();
                return true;
            }
            $inserts = [];
            $langIdInserts = [];
            foreach ($langIds as $langId) {
                if (in_array($langId, $langIdInserts)) {
                    continue;
                }
                $langIdInserts[] = $langId;
                $inserts[] = [
                    'proj_exper_id' => $projExperId,
                    'lang_id' => $langId
                ];
            }
            self::insert($inserts);
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback;
            throw $ex;
        }
    }

    /**
     * get lang of project experience
     *
     * @param type $projExperId
     * @return type
     */
    public static function getLangOfProjExper($projExperId)
    {
        $tblProjExperLang = self::getTableName();
        $tblTag = Tag::getTableName();
        return self::select([$tblProjExperLang . '.lang_id', 't_tag.value as lang_name'])
            ->join($tblTag . ' AS t_tag', 't_tag.id', '=',$tblProjExperLang . '.lang_id')
            ->where($tblProjExperLang . '.proj_exper_id', $projExperId)
            ->get();
    }

    /**
     * insert lang of project experience of employee
     *
     * @param int $projExperId
     * @param array $tagTypes ['os' => [1,2,3], ['lang'] => [4,5,6]]
     * @return boolean
     * @throws Exception
     */
    public static function saveProjExperTag($projExperId, $tagTypes = [], $langCur = null)
    {
        $typeTagsAvai = ['lang', 'os', 'other', 'res', 'role'];
        $tagTypes = array_filter($tagTypes, function ($key) use ($typeTagsAvai) {
                return in_array($key, $typeTagsAvai);
            },
            ARRAY_FILTER_USE_KEY
        );
        self::where('proj_exper_id', $projExperId)
            ->where(function ($query) use ($langCur) {
                $query->orWhereNull('lang')
                    ->orWhere('lang', $langCur);
            })
            ->delete();
        if (!$tagTypes) {
            return true;
        }
        $inserts = [];
        foreach ($tagTypes as $key => $ids) {
            $lang = in_array($key, ['res', 'role']) ? $langCur : null;
            if ($key === 'role') {
                $inserts[] = [
                    'proj_exper_id' => $projExperId,
                    'tag_id' => $ids,
                    'tag_text' => '',
                    'type' => $key,
                    'lang' => $lang
                ];
                continue;
            }
            if (count($ids) === 1 && reset($ids) == -1) {
                unset($tagTypes[$key]);
                continue;
            }
            foreach ($ids as $id) {
                $tagId = $tagText = null;
                if (is_numeric($id)) {
                    $tagId = $id;
                } else {
                    $tagText = preg_replace('/^n\-/', '', $id);
                }
                $inserts[] = [
                    'proj_exper_id' => $projExperId,
                    'tag_id' => $tagId,
                    'tag_text' => $tagText,
                    'type' => $key,
                    'lang' => $lang
                ];
            }
        }
        // get tagids avai
        /*$collectionTag = Tag::select('id')
            ->whereIn('id', $tagIdsSubmit)
            ->get();
        $tagIdsDb = [];
        foreach ($collectionTag as $item) {
            $tagIdsDb[] = $item->id;
        }
        if (!$tagIdsDb) {
            return true;
        }*/
        if (count($inserts)) {
            self::insert($inserts);
        }
    }

    /**
     * get skill of project experience
     *
     * @param type $collection
     * @return array
     */
    public static function getSkillsProjInCv($collection)
    {
        $result = [
            'tag' => []
        ];
        if (!count($collection)) {
            return $result;
        }
        $projIds = [];
        foreach ($collection as $item) {
            $projIds[] = $item->id;
        }
        $tblProjTag = self::getTableName();
        $tblTag = Tag::getTableName();
        $tagCollection = self::select([$tblProjTag . '.proj_exper_id',
            $tblProjTag . '.tag_id', $tblProjTag . '.type', 't_tag.value'])
            ->join ($tblTag . ' AS t_tag', 't_tag.id', '=', $tblProjTag . '.tag_id')
            ->whereNull('t_tag.deleted_at')
            ->whereIn('proj_exper_id', $projIds)
            ->get();
        if (!count($tagCollection)) {
            return $result;
        }
        foreach ($tagCollection as $item) {
            $result[$item->proj_exper_id][$item->type][] = $item->tag_id;
            $result['tag'][$item->tag_id] = $item->value;
        }
        return $result;
    }

    /**
     * get skill ids follow group of project experience
     *
     * @param collection $collection
     * @return array
     */
    public static function getSkillIdsProjInCv($collection, $locale = null)
    {
        $result = [
            'data' => [],
            'tag_ids' => []
        ];
        if (!count($collection)) {
            return $result;
        }
        $projIds = [];
        foreach ($collection as $item) {
            $projIds[] = $item->id;
        }
        $tagCollection = self::select(['proj_exper_id', 'tag_id', 'type',
            'tag_text', 'lang'])
            ->whereIn('proj_exper_id', $projIds);
        //export check locale
        if ($locale) {
            // only type res has multi language
            // where ((type = res and (tag_id is not null or lang = $locale)) or type != 'res')
            $tagCollection->where(function ($query) use ($locale) {
                $query->where(function ($subQuery) use ($locale) {
                    $subQuery->where('type', 'res')
                        ->where(function ($subQuery2) use ($locale) {
                            $subQuery2->whereNotNull('tag_id')
                                ->orWhere('lang', $locale);
                        });
                })
                ->orWhere('type', '!=', 'res');;
            });
        }
        $tagCollection = $tagCollection->get();
        if (!count($tagCollection)) {
            return $result;
        }
        foreach ($tagCollection as $item) {
            $itemResult = [
                'id' => $item->tag_id,
                'text' => $item->tag_text,
                'lang' => $item->lang,
            ];
            $result['data'][$item->proj_exper_id][$item->type][] = $itemResult;
            if ($item->tag_id && !in_array($item->tag_id, $result['tag_ids'])) {
                $result['tag_ids'][] = $item->tag_id;
            }
        }
        return $result;
    }

    /**
     * remove project tags
     *
     * @param array $projIds
     * @return boolean
     */
    public static function removeProj($projIds = [])
    {
        if (!$projIds) {
            return true;
        }
        self::whereIn('proj_exper_id', $projIds)
            ->delete();
    }

    /**
    * Set the keys for a save update query.
    *
    * @param  \Illuminate\Database\Eloquent\Builder  $query
    * @return \Illuminate\Database\Eloquent\Builder
    */
    protected function setKeysForSaveQuery(Builder $query) {
        $keys = $this->getKeyName();
        if (!is_array($keys)) {
            return parent::setKeysForSaveQuery($query);
        }

        foreach ($keys as $keyName) {
            $query->where($keyName, '=', $this->getKeyForSaveQuery($keyName));
        }

        return $query;
    }

    /**
     * Get the primary key value for a save query.
     *
     * @param mixed $keyName
     * @return mixed
     */
    protected function getKeyForSaveQuery($keyName = null) {
        if (is_null($keyName)) {
            $keyName = $this->getKeyName();
        }

        if (isset($this->original[$keyName])) {
            return $this->original[$keyName];
        }

        return $this->getAttribute($keyName);
    }

}
