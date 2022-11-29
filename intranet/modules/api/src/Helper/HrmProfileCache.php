<?php

namespace Rikkei\Api\Helper;


use Rikkei\Api\Models\CacheHrmProfile;

/**
 * Helper to get and set CacheHrmProfile
 *
 */
class HrmProfileCache extends HrmBase
{

    const KEY_PERSONAL = 'personal';
    const KEY_TEAM_HISTORY = 'team-history';
    const KEY_CONTRACT = 'contract';
    const KEY_OTHER = 'other';
    const KEY_MILITARY = 'military';
    const KEY_SKILL_SHEET_SUMMARY = 'skill-sheet-summary';
    const KEY_SKILL_SHEET_PROJECT = 'skill-sheet-project';
    const KEY_SKILL_SHEET_SKILL = 'skill-sheet-skill';
    const KEY_RESET = 'reset';

    public static function getAvailableKey()
    {
        return [
            self::KEY_PERSONAL,
            self::KEY_TEAM_HISTORY,
            self::KEY_CONTRACT,
            self::KEY_OTHER,
            self::KEY_MILITARY,
            self::KEY_SKILL_SHEET_SUMMARY,
            self::KEY_SKILL_SHEET_PROJECT,
            self::KEY_SKILL_SHEET_SKILL,
        ];
    }

    /**
     * @param $key
     * @return mixed
     */
    public static function _getObjectByKey($key)
    {
        return CacheHrmProfile::where('key', $key)->first();
    }

    /**
     * @param $key
     * @param $value
     * @return bool
     */
    public static function put($key, $value)
    {
        $value = serialize($value);
        $model = self::_getObjectByKey($key);
        if ($model) {
            CacheHrmProfile::where('key', $key)->update([
                'value_serialize' => $value
            ]);
        } else {
            CacheHrmProfile::create([
                'key' => $key,
                'value_serialize' => $value
            ]);
        }

        return true;
    }

    /**
     * @param $key
     * @return |null
     */
    public static function get($key)
    {
        $model = self::_getObjectByKey($key);

        return $model ? unserialize($model->value_serialize) : null;
    }

    /**
     * @param $key
     * @return bool
     */
    public static function forget()
    {
        CacheHrmProfile::truncate();

        return true;
    }
}
