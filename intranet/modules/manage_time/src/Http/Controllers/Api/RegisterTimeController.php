<?php

namespace Rikkei\ManageTime\Http\Controllers\Api;

use Rikkei\Core\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Rikkei\ManageTime\View\CollectEmp;
use Rikkei\Team\Model\Team;
use Rikkei\ManageTime\View\ManageTimeConst;
use Validator;

class RegisterTimeController extends Controller
{
    /*
     * list employee not register leave or onsite on special date
     */
    public function listRegNotLeaveOrOnsite(Request $request)
    {
        $valid = Validator::make($request->all(), [
           'date' => 'required|date_format:d/m/Y'
        ]);
        if ($valid->fails()) {
            return response()->json([
                'success' => 0,
                'message' => $valid->messages()
            ]);
        }

        $excludeTypes = [ManageTimeConst::TYPE_LEAVE_DAY, ManageTimeConst::TYPE_MISSION];
        $data = $request->all();
        $data['exclude_types'] = $excludeTypes;
        if (!isset($data['team_code'])) {
            $data['team_code'] = Team::CODE_PREFIX_DN;
        }

        return [
            'success' => 1,
            'employees' => CollectEmp::listRegistrationTimes($data)
        ];
    }
}

