<?php

namespace Rikkei\Api\Http\Controllers\Project;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\Project\Model\Timesheet;

class TimesheetController extends Controller
{
    /**
     * API get timesheets by project_id, po_id, start_date, end_date
     *
     * @param Request $request
     * @return array
     */
    public function getTimesheet(Request $request)
    {
        $data = $request->all();

        // validate data
        $validator = $this->validateParam($data);
        if ($validator->fails()) {
            $errors = $validator->errors()->all();

            return response()->json([
                'status' => 'error',
                'message' => 'Tham số không hợp lệ',
                'errors' => $errors
            ]);
        }

        $timeSheet = Timesheet::instance()->getTimesheetForApi($data);

        return
            response()->json([
                'status' => 'success',
                'code' => Response::HTTP_OK,
                'data' => $timeSheet
            ]);
    }

    /**
     * Validate data
     *
     * @param $request
     * @return mixed
     */
    private function validateParam($request)
    {
        return Validator::make($request, [
            'po_id' => 'required|string|min:36|max:36',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
        ]);
    }
}
