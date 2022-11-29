<?php

namespace Rikkei\Project\Model;

use Rikkei\Core\Model\CoreModel;
use Illuminate\Support\Facades\DB;
use Rikkei\ManageTime\View\ManageTimeCommon;
use Rikkei\Team\Model\Employee;

class EmployeeContractMember extends CoreModel
{
    const DECIMALS_POINT = 2;

    protected $table = 'employee_contract_members';

    protected $fillable = ['employee_id', 'month', 'point', 'team_id'];

    /**
     * get data point update
     * @param $data
     *
     * @return boolean
     */
    public static function getDataPointUpdate($request)
    {
        $item = self::where([
                    ['employee_id', $request->dataEmployeeId],
                    ['month', $request->dataMonth],
                    ['team_id', $request->teamId]
                ])->first();

        if (!$item) {
            self::create([
                'point' => $request->point,
                'employee_id' => $request->dataEmployeeId,
                'month' => $request->dataMonth,
                'team_id' => $request->teamId
            ]);

            return true;
        }

        $item->point = $request->point;
        $item->save();

        return true;
    }
}
