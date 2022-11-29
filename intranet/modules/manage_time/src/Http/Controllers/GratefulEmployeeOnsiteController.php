<?php
namespace Rikkei\ManageTime\Http\Controllers;

use Exception;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Rikkei\Team\View\Permission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\ManageTime\Model\GratefulEmployeeOnsite;

class GratefulEmployeeOnsiteController extends Controller
{
     
    protected $modelGrateful;

    public function __construct(GratefulEmployeeOnsite $modelGrateful)
    {
        $this->modelGrateful = $modelGrateful;
    }
    
    /**
     * store
     *
     * @param  Request $request
     * @return json
     */
    public function store(Request $request)
    {
        $data = $request->all();
        $userCurrent = Permission::getInstance()->getEmployee();
        $dataEmployee = [];

        DB::beginTransaction();
        try {
            foreach($data['arrItem'] as $item) {
                $arr = explode('_', $item);
                $dataEmployee[$arr[0]] = $arr[1];
                if(!$this->modelGrateful->findEmployee($arr[0], $arr[1])) {
                    $this->modelGrateful->create([
                        'employee_id' => $arr[0],
                        'number' => $arr[1],
                        'date_grateful' => Carbon::parse($data['date']),
                        'created_by' => $userCurrent->id,
                        'note' => $data['note'],
                    ]);
                }
            }
            DB::commit();
            return response()->json([
                'status' => 1,
                'message' => 'Lưu thành công',
            ]);
        } catch(Exception $e) {
            DB::rollback();
            Log::info($e);
            return response()->json([
                'status' => 0,
                'message' => 'Có lỗi sảy ra. Bạn vui lòng thử lại sau!'
            ]);
        }
    }
    
    
    /**
     * remove grateful
     *
     * @param  Request $request
     * @return json
     */
    public function remove(Request $request)
    {
        $data = $request->all();
        $userCurrent = Permission::getInstance()->getEmployee();
        $dataEmployee = [];
        
        if (!isset($data['note']) || (isset($data['note']) && $data['note'] == '')) {
            return response()->json([
                'status' => 0,
                'message' => 'Bạn cần nêu lý do bỏ tri ân.',
            ]);
        }
        DB::beginTransaction();
        try {
            foreach($data['arrItem'] as $item) {
                $arr = explode('_', $item);
                $dataEmployee[$arr[0]] = $arr[1];
                $employee = $this->modelGrateful->findEmployee($arr[0], $arr[1]);
                if($employee) {
                    $employee->update([
                        'created_by' => $userCurrent->id,
                        'note' => $employee->note . "\n Lý do bỏ tri ân: " . $data['note'],
                        'deleted_at' => Carbon::now(),
                    ]);
                }
            }
            DB::commit();
            return response()->json([
                'status' => 1,
                'message' => 'Lưu thành công',
            ]);
        } catch(Exception $e) {
            DB::rollback();
            Log::info($e);
            return response()->json([
                'status' => 0,
                'message' => 'Có lỗi sảy ra. Bạn vui lòng thử lại sau!'
            ]);
        }
    }
}