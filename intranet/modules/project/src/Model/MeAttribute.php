<?php

namespace Rikkei\Project\Model;

use Illuminate\Support\Facades\Session;
use Rikkei\Core\Model\CoreModel;
use Rikkei\Core\Model\Menu;
use Rikkei\Core\View\CacheHelper;
use Rikkei\Project\Model\MeAttributeLang;
use Rikkei\Team\View\Config;
class MeAttribute extends CoreModel
{
    protected $table = 'me_attributes';
    protected $fillable = ['weight', 'order', 'range_min', 'range_max', 'range_step', 'group', 'can_fill', 'default', 'type'];
    protected $appends = ['group_label'];

    public $timestamps = false;

    const GR_PERFORM = 2;
    const GR_NORMAL = 1;
    const GR_NEW_PERFORM = 3;
    const GR_NEW_NORMAL = 4;

    const EXCELLENT = 5;
    const GOOD = 4;
    const FAIR = 3;
    const SATIS = 2;
    const UNSATIS = 1;
    const NA = -1;

    const KEY_TIME_SHEET = 'me_attribute_timesheet';
    const POINT_MAX_REG = 10;

    const TYPE_REGULATIONS = 1;
    const TYPE_PRO_ACTIVITY = 2;
    const TYPE_SOCIAL_ACTIVITY = 3;
    const TYPE_CUSTOMER_FEEDBACK = 4;
    const TYPE_WORK_QUALITY = 5;
    const TYPE_WORK_PROGRESS = 6;
    const TYPE_WORK_PROCESS = 7;
    const TYPE_TEAMWORK = 8;
    const TYPE_WORK_PERFORM = 10;
    const TYPE_NEW_PRO_ACTIVITY = 20;
    const TYPE_NEW_REGULATIONS = 21;

    const KEY_CACHE_NORMAL = 'key_me_attribute_normal';
    const KEY_CACHE_PERFORM = 'key_me_attribute_perform';

    const CACHE_LANG = 'cacheLang';

     /**
     * Get the atribitelang
     */
    public function attribuiteLangByLang($lang)
    {
        return $this->hasMany(MeAttributeLang::class, 'attr_id', 'id')
            ->where('lang_code', $lang)->first();
    }

    public static function getGroupTypes()
    {
        return [
            self::GR_NORMAL => trans('project::view.Normal'),
            self::GR_PERFORM => trans('project::view.Performance'),
            self::GR_NEW_NORMAL => 'New Normal',
            self::GR_NEW_PERFORM => 'New Performance',
        ];
    }

    public static function getAll($groups = [self::GR_PERFORM, self::GR_NORMAL])
    {
        $lang = Session::get('locale');
        return self::orderBy('group', 'asc')
            ->join('me_attribute_lang', 'me_attributes.id', '=', 'me_attribute_lang.attr_id')
            ->select('me_attributes.*', 'me_attribute_lang.name', 'me_attribute_lang.label', 'me_attribute_lang.description')
            ->where('lang_code', '=', $lang)
            ->whereIn('group', $groups)
            ->groupBy('me_attributes.id')
            ->orderBy('order', 'asc')
            ->get();
    }

    public static function getFirstAutoFill()
    {
        return self::where('can_fill', 0)->first();
    }

    public static function getGridData()
    {
        $lang = Session::get('locale');
        $pager = Config::getPagerData();
        $pager['order'] = 'group';
        $pager['dir'] = 'asc';

        $collection = self::select('me_attributes.*', 'me_attribute_lang.name', 'me_attribute_lang.label', 'me_attribute_lang.description')
            ->join('me_attribute_lang', 'me_attribute_lang.attr_id', '=', 'me_attributes.id')
            ->where('lang_code', '=', $lang)
            ->groupBy('me_attributes.id')
            ->orderBy($pager['order'], $pager['dir'])
            ->orderBy('order', 'asc');
        self::filterGrid($collection);
        self::pagerCollection($collection, $pager['limit'], $pager['page']);

        return $collection;
    }

    public function getGroupLabelAttribute()
    {
        if ($this->group == self::GR_NORMAL) {
            return trans('project::view.Normal');
        }
        return trans('project::view.Performance');
    }

    /**
     * get list normal attributes
     * @return type
     */
    public static function getNormalAttrs()
    {
        return self::getByGroup(self::GR_NORMAL);
    }

    /**
     * get list perform attributes
     * @return type
     */
    public static function getPerformAttrs()
    {
        return self::getByGroup(self::GR_PERFORM);
    }

    /**
     * get attribute by group
     * @param mix:array|integer $group
     * @return collection
     */
    public static function getByGroup($group)
    {
        $groups = is_array($group) ? $group : [$group];
        $lang = Session::get('locale');
        $keyCache = 'me_attr_group_' . implode('_', $groups) . '_' . $lang;
        if ($list = CacheHelper::get($keyCache)) {
            return $list;
        }

        $list = self::whereIn('group', $groups)
            ->join('me_attribute_lang', 'me_attributes.id', '=', 'me_attribute_lang.attr_id')
            ->where('lang_code', '=', $lang)
            ->select('me_attributes.*', 'me_attribute_lang.name', 'me_attribute_lang.label', 'me_attribute_lang.description')
            ->groupBy('me_attributes.id')
            ->orderBy('order', 'asc')
            ->get();
        CacheHelper::put($keyCache, $list);

        return $list;
    }

    public function save(array $options = array())
    {
        parent::save($options);
        CacheHelper::forget(self::KEY_CACHE_NORMAL);
        CacheHelper::forget(self::KEY_CACHE_PERFORM);
    }

    /**
     * get list option and label point
     * @return type
     */
    public static function optionPoints($hasNA = false)
    {
        $options = [
            self::EXCELLENT => trans('project::me.Excellent'),
            self::GOOD => trans('project::me.Good'),
            self::FAIR => trans('project::me.Fair'),
            self::SATIS => trans('project::me.Satisfactory'),
            self::UNSATIS => trans('project::me.Unsatisfactory')
        ];
        if ($hasNA) {
            $options[self::NA] = 'N/A';
        }
        return $options;
    }

    /**
     * get me timesheet attribute id
     * @return int/null
     */
    public static function getMeTimeAttrId()
    {
        if ($id = CacheHelper::get(self::KEY_TIME_SHEET)) {
            return $id;
        }
        $item = self::where('can_fill', 0)
            ->first();
        if ($item) {
            CacheHelper::put(self::KEY_TIME_SHEET, $item->id);
            return $item->id;
        }
        return null;
    }

    /*
     * get activity fields
     */
    public static function getFieldActivity($types = [self::TYPE_PRO_ACTIVITY, self::TYPE_SOCIAL_ACTIVITY])
    {
        $lang = Session::get('locale');
        return self::whereIn('type', $types)
            ->select('me_attributes.*', 'me_attribute_lang.name', 'me_attribute_lang.label', 'me_attribute_lang.description')
            ->join('me_attribute_lang', 'me_attributes.id', '=', 'me_attribute_lang.attr_id')
            ->where('lang_code', '=', $lang)
            ->groupBy('me_attributes.id')
            ->get();
    }

}
