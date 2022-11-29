<?php

namespace Rikkei\AdminSetting\Model;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Rikkei\Core\Model\CoreModel;
use Rikkei\Core\Model\EmailQueue;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\Model\Team;
use Rikkei\Team\View\Config;
use Illuminate\Database\Eloquent\SoftDeletes;
use Rikkei\Core\Model\User;
use Rikkei\Team\Model\TeamMember;

class AdminOtDisallow extends CoreModel
{
    use SoftDeletes;
    protected $table = 'ot_disallow';

    /**
     * get collection to show grid data
     * @return collection model
     */
    // $collection->WHERE deleted_at IS NULL (SoftDeletes)
    public static function getGridData($teamId = null)
    {
        $pager = Config::getPagerData();
        $collection = AdminOtDisallow::groupBy('division');
        if ($teamId) {
            $collection->whereIn('division', (array) $teamId);
        }
        $collection->orderBy($pager['order'], $pager['dir']);
        self::pagerCollection($collection, $pager['limit'], $pager['page']);
        return $collection;
    }

    /**
     * get model by division
     * @return array
     */
    public static function getByDivision($division, $getArrDivision = false)
    {
        $result = self::where('division', '=', $division)->first();
        if ($getArrDivision && isset($result)) {
            return explode(',', str_replace(['{', '}'], '', $result['admin']));
        }
        return $result;
    }

    public static function getById($id)
    {
        return self::find($id);
    }

    public static function getEmployeeOtDivision($teamIds = null)
    {
        $collectionModel = AdminOtDisallow::getGridData($teamIds);
        foreach ($collectionModel as $value) {
            $nameArray = [];
            $value->employee_id = str_replace(['{', '}'], '', $value->employee_id);
            $value->employee_id = explode(',', $value->employee_id);
            if (count($value->employee_id) > 0) {
                foreach ($value->employee_id as $val) {
                    array_push($nameArray, Employee::getNameEmpById($val));
                }
                $value->employee_id = implode(', ', $nameArray);
            }
            $value->team_id = $value->division;
            $value->division = Team::getTeamNameById($value->division);

        
        }

        return $collectionModel;
    }

}
