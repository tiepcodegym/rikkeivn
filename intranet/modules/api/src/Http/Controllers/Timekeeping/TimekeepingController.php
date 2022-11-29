<?php
namespace Rikkei\Api\Http\Controllers\Timekeeping;

use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Log;
use Rikkei\Api\Helper\Timekeeping;
use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\ManageTime\View\ViewTimeKeeping;
use Illuminate\Support\Facades\Validator;


class TimekeepingController extends Controller
{
    public function getTimeInOut(Request $request)
    {
        try {
            $date = $request->date;
            if (!$date) {
                $cbDate = Carbon::now();
            } else {
                $cbDate = Carbon::createFromFormat('Y-m-d', $date);
            }
            $date = $cbDate->format('Y_m_d');
            $tk = new ViewTimeKeeping();
            $result = $tk->getTimeInOutEmployee($date);

            return response()->json($result);
        } catch (Exception $ex) {
            Log::info($ex);
            return response()->json([
                'success' => 0,
                'message' => Timekeeping::getInstance()->errorMessage($ex),
            ]);
        }
    }

    public function updateRelatedPerson(Request $request)
    {
        $params = $request->all();
        $rules = [
            'current_user_id' => 'required|integer|exists:employees,id',
            'p' => 'array',
            'bsc' => 'array',
            'ot' => 'array',
            'ct' => 'array',
        ];
        $validator = Validator::make($params, $rules);
        if ($validator->fails()) {
            return response()->json([
                'success' => 0,
                'message' => trans('api::message.Error input data!'),
                'errors' => $validator->errors(),
            ]);
        }
        $c = 0;
        if (isset($params['p']) || isset($params['bsc']) || isset($params['ot']) || isset($params['ct'])) {
            foreach ($params as $key => $item) {
                if (in_array($key, ['p', 'bsc', 'ot', 'ct'])) {
                    if (!empty($params[$key][0])) {
                        $c++;
                    }
                }
            }
        }
        if ($c == 0) {
            $validator = validator()->make([], []);
            $validator->after(function ($vld) {
                $vld->errors()->add('record_id', 'Phải nhập ít nhất 1 mảng id loại đơn');
            });
            return response()->json([
                'success' => 0,
                'message' => $validator->messages()
            ]);
        }

        try {
            return Timekeeping::getInstance()->updateRelatedPerson($params);
        } catch (\Exception $ex) {
            \Log::info($ex);
            return [
                'success' => 0,
                'message' => Timekeeping::getInstance()->errorMessage($ex)
            ];
        }
    }
}