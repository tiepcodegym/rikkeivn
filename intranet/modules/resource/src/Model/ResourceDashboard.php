<?php
namespace Rikkei\Resource\Model;

use DB;
use Rikkei\Core\Model\CoreModel;
use Log;

class ResourceDashboard extends CoreModel
{

    protected $table = 'resource_dashboard';

    const TOTAL_EMPLOYEE_EFFORT = 1;
    const COUNT_EMPLOYEE_PLAN = 2;
    const TOTAL_MAN_MONTH = 3;
    const COUNT_EMPLOYEE = 4;
    const TOTAL_ROLE = 5;
    const COUNT_PROLANG = 6;
    const MM_PROJECT = 7;
    const TOTAL_MAN_MONTH_NO_BORROW = 8;

    protected $fillable = ['data', 'created_at', 'updated_at', 'type', 'team_id'];

    static function listChartKey()
    {
        return [
            self::TOTAL_EMPLOYEE_EFFORT,
            self::COUNT_EMPLOYEE_PLAN,
            self::TOTAL_MAN_MONTH,
            self::COUNT_EMPLOYEE,
            self::TOTAL_ROLE,
            self::COUNT_PROLANG,
            self::MM_PROJECT,
            self::TOTAL_MAN_MONTH_NO_BORROW,
        ];
    }

    public static function updateData($data)
    {
        DB::beginTransaction();
        try {
            if (isset($data['id'])) {
                self::where('id', $data['id'])->update($data);
            } else {
                self::insert($data);
            }
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            Log::info($ex);
        }
    }

    /**
     * Get record
     * @param type $type
     * @param type $year
     * @param type $teamId
     * @return ResourceDashboard
     */
    public static function getByTypeAndTeam($type, $year, $teamId = null)
    {
        $result = self::where('type', $type)->where('year', $year);
        if ($teamId) {
            $result->where('team_id', $teamId);
        } else {
            $result->whereNull('team_id');
        }
        $result->select('*');
        return $result->first();
    }

    /**
     * Get distinct year
     */
    public static function getListYears()
    {
        return self::distinct()->orderBy('year')->get(['year']);
    }
}
