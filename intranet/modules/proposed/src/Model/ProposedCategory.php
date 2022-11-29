<?php

namespace Rikkei\Proposed\Model;

use Rikkei\Core\View\Form;
use Rikkei\Core\Model\CoreModel;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\View\Config;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Lang;

class ProposedCategory extends CoreModel
{
    use SoftDeletes;

    protected $fillable = [
        'name_vi',
        'name_en',
        'name_ja',
        'created_by',
        'status'
    ];

    const STATUS_ACTIVE = 1;
    const STATUS_NOT_ACTIVE = 2;

    /**
     *
     */
    public function employees()
    {
        return $this->belongsTo(Employee::class, 'created_by');
    }

    /**
     * get index prpposed category by country code
     * @param  [int] $idCountry
     * @return [collection]
     */
    public function index()
    {
        $tblProCate = ProposedCategory::getTableName();
        $tblEmp = Employee::getTableName();

        $collections = ProposedCategory::select(
            "{$tblProCate}.*",
            "{$tblEmp}.name as nameEmp"
        )
        ->leftJoin("{$tblEmp}", "{$tblEmp}.id", '=', "{$tblProCate}.created_by")
        ->whereNull("{$tblProCate}.deleted_at")
        ->orderBy($tblProCate . '.created_at', 'DESC');

        $pager = Config::getPagerData();
        self::pagerCollection($collections, $pager['limit'], $pager['page']);
        return $collections;
    }

    /**
     * [getStatus description]
     * @return [type] [description]
     */
    public static function getStatus()
    {
        return [
            static::STATUS_ACTIVE => Lang::get('proposed::view.Active'),
            static::STATUS_NOT_ACTIVE => Lang::get('proposed::view.Not active'),
        ];
    }

    /**
     * [getlistProCategoies description]
     * @return [type]
     */
    public static function getlistProCategoies()
    {
        return static::select('id', 'name_vi', 'name_en', 'name_ja')
            ->whereNull('deleted_at')
            ->get();
    }
}
