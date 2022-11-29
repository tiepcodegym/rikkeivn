<?php

namespace Rikkei\Test\Models;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Core\Model\ArrayPrimaryKeyTrait;

class LangGroup extends CoreModel
{
    protected $table = 'ntest_lang_group';
    protected $fillable = ['group_id', 'test_id', 'lang_code'];
    protected $primaryKey = ['group_id', 'test_id'];
    public $incrementing  = false;

    use ArrayPrimaryKeyTrait;

    /**
     * list test items same group by current test id
     *
     * @param int $testId
     * @return collection [lang_code => test item]
     */
    public static function listTestsByLangGroup($testId, $groupLangCode = true)
    {
        $testTbl = Test::getTableName();
        $groupTbl = self::getTableName();
        $collect = Test::select($testTbl . '.id', $testTbl . '.name', 'group.lang_code', 'group.group_id')
            ->join($groupTbl . ' as group', 'group.test_id', '=', $testTbl . '.id')
            ->whereIn('group.group_id', function ($query) use ($testId, $groupTbl) {
                $query->select('group_id')
                    ->from($groupTbl)
                    ->where('test_id', $testId);
            })
            ->get();
        if (!$groupLangCode) {
            return $collect;
        }
        return $collect->groupBy('lang_code')
            ->map(function ($items) {
                return $items->first();
            });
    }

    /**
     * list test ids same group by current testid
     *
     * @param int $testId
     * @return array
     */
    public static function listTestIdsSameGroup($testId, $exceptId = null)
    {
        $list = self::whereIn('group_id', function ($query) use ($testId) {
            $query->select('group_id')
                ->from(self::getTableName())
                ->where('test_id', $testId);
        });
        if ($exceptId) {
            $list->where('test_id', '!=', $exceptId);
        }
        return $list->pluck('test_id', 'lang_code')
            ->toArray();
    }

    /**
     * update group test id
     *
     * @param array $groupTestIds
     */
    public static function updateTestIds($groupTestIds, $groupId = null)
    {
        if ($groupId) {
            //remove old data
            self::where('group_id', $groupId)
                ->whereIn('test_id', $groupTestIds)
                ->delete();
        } else {
            $groupId = (int) self::max('group_id') + 1;
        }
        //insert data
        $dataInsert = [];
        foreach ($groupTestIds as $langCode => $testId) {
            if (isset($dataInsert[$langCode]) || !$testId) {
                continue;
            }
            $dataInsert[$langCode] = [
                'group_id' => $groupId,
                'test_id' => $testId,
                'lang_code' => $langCode
            ];
        }
        if (count($dataInsert) > 0) {
            self::insert($dataInsert);
        }
    }

    public static function collectTestLangFromTestId($testId)
    {
        $groupTbl = self::getTableName();
        return LangGroup::select('group.test_id')
            ->from($groupTbl . ' as group')
            ->whereIn('group.group_id', function ($query) use ($testId, $groupTbl) {
                $query->select('group_id')
                    ->from($groupTbl)
                    ->where('test_id', $testId);
            })
            ->where('group.test_id', '!=', $testId)
            ->groupBy('group.test_id')
            ->get();
    }
}
