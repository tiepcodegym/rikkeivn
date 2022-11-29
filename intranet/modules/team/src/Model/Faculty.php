<?php
namespace Rikkei\Team\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Core\View\CacheHelper;

class Faculty extends CoreModel
{   
    const KEY_CACHE = 'school_faculty';

    protected $table = 's_faculties';
    public $timestamps = false;

    /**
     * get all major
     *
     * @return string array
     */
    public static function getFacultyList()
    {
        if ($schools = CacheHelper::get(self::KEY_CACHE)) {
            return $schools;
        }
        $schools = self::select(['id', 'name'])->orderBy('sort_order')
            ->orderBy('name')->get();
        if (!count($schools)) {
            return [];
        }
        $result = [];
        foreach ($schools as $school) {
            $result[$school->id] = $school->name;
        }
        CacheHelper::put(self::KEY_CACHE, $result);
        return $result;
    }
}
