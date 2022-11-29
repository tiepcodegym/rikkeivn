<?php
namespace Rikkei\Recruitment\Model;

use Rikkei\Core\Model\CoreModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Rikkei\Team\Model\Employee;

class RecruitmentApplies extends CoreModel
{
    
    use SoftDeletes;
    
    protected $table = 'recruitment_applies';
    
    /**
     * get presenter name follow phone
     * 
     * @param string|int $phoneOrId
     * @return string
     */
    public static function getPresenterName($phoneOrId, $isPhone = true)
    {
        $employeeTable = Employee::getTableName();
        $recruimentApplyTable = self::getTableName();
        $recruitmentApply = self::select("{$employeeTable}.name as e_name")
            ->join($employeeTable, "{$employeeTable}.id", '=', "{$recruimentApplyTable}.presenter_id");
        if ($isPhone) {
            $recruitmentApply->where('phone', $phoneOrId);
        } else {
            $recruitmentApply->where("{$recruimentApplyTable}.id", $phoneOrId);
        }
        $recruitmentApply = $recruitmentApply->first();
        if ($recruitmentApply) {
            return $recruitmentApply->e_name;
        }
        return '';
    }
    
    /**
     * get presenter name follow phone
     * 
     * @param int $idApply
     * @return string
     */
    public static function getPresenterNameFromId($idApply)
    {
        $recruitmentApply = self::find($idApply);
        if (! $recruitmentApply) {
            return;
        }
        $employeeTable = Employee::getTableName();
        $recruimentApplyTable = self::getTableName();
        $recruitmentApply = self::select("{$employeeTable}.name as e_name")
            ->join($employeeTable, "{$employeeTable}.id", '=', "{$recruimentApplyTable}.presenter_id")
            ->where('phone', $phone)
            ->first();
        if ($recruitmentApply) {
            return $recruitmentApply->e_name;
        }
        return '';
    }
    
}