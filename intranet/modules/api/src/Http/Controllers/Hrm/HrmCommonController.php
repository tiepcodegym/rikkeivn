<?php

namespace Rikkei\Api\Http\Controllers\Hrm;

use Illuminate\Http\Request;
use Log;
use Rikkei\Api\Helper\HrmCommon as HrmCommonHelper;
use Rikkei\Core\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Rikkei\Contract\Model\ContractModel;
use Rikkei\Team\Model\Employee;
use Rikkei\Resource\View\getOptions;
use Carbon\Carbon;
use Rikkei\Api\Requests\CreateContractRequest;
use Rikkei\Api\Requests\UpdateContractRequest;
use Illuminate\Support\Facades\DB;
use Rikkei\Team\Model\Team;
use Rikkei\Education\Model\EducationTeacher;
use Rikkei\Education\Model\EducationTeacherTime;
use Rikkei\Education\Http\Services\RegisterTeachingService;

class HrmCommonController extends Controller
{
    protected $registerTeachingService;

    public function __construct(RegisterTeachingService $registerTeachingService)
    {
        $this->registerTeachingService = $registerTeachingService;
    }

    /**
     * API - Get all branches
     *
     * @param Request $request for attributes
     * @return array json
     */
    public function getBranches(Request $request)
    {
        try {
            $response = HrmCommonHelper::getInstance()->getBranches();
            return [
                'success' => 1,
                'data' => $response
            ];
        } catch (\Exception $ex) {
            Log::info($ex);
            return [
                'success' => 0,
                'message' => HrmCommonHelper::getInstance()->errorMessage($ex)
            ];
        }
    }

    public function saveContract(Request $request)
    {
        $params = $request->all();
        $hrmContractId = $request->get('hrm_contract_id');
        $email = $request->get('employee_email');
        $creator = $request->get('creator');

        $dataEmployee = Employee::findByEmail($email);
        $dataCreator = Employee::findByEmail($creator, true);
        if (!$dataEmployee) {
            return [
                'success' => 0,
                'message' => 'Không tìm thấy bản ghi của nhân viên!'
            ];
        }
        if (!$dataCreator) {
            return [
                'success' => 0,
                'message' => 'Không tìm thấy bản ghi của người tạo!'
            ];
        }
        $empId = $dataEmployee->id;
        $creatorId = $dataCreator->id;

        $contract = ContractModel::where('hrm_contract_id', $hrmContractId)->first();
        if ($contract) {
            //validate update
            $id = $contract->id;
            $contractInfo = ContractModel::getContractById($id);
            if ($contractInfo && !$contractInfo->isContractLast()) {
                return [
                    'success' => 0,
                    'message' => 'Bản ghi cũ không được phép chỉnh sửa!'
                ];
            } else {
                $updateValidate = UpdateContractRequest::validate($params, $empId, $id);
                if ($updateValidate) {
                    return $updateValidate;
                }
            }
            $submit = 'update';
        } else {
            //validate create
            $createValidate = CreateContractRequest::validate($params, $empId);
            if ($createValidate) {
                return $createValidate;
            }
            $submit = 'create';
        }

        try {
            $params['empId'] = $empId;
            $params['creatorId'] = $creatorId;
            HrmCommonHelper::getInstance()->saveContract($params, $hrmContractId, $submit);
            $text = 'Tạo mới thành công!';
            if ($contract) {
                $text = 'Cập nhật thông tin hợp đồng thành công!';
            }
            return [
                'success' => 1,
                'data' => $text,
            ];
        } catch (\Exception $ex) {
            \Log::info($ex);
            return [
                'success' => 0,
                'message' => HrmCommonHelper::getInstance()->errorMessage($ex)
            ];
        }
    }

    public function deleteContract(Request $request)
    {
        $params = $request->all();        
        $rules = [
            'hrm_contract_id' => "required|string",
        ];
        $validator = Validator::make($params, $rules);
        if ($validator->fails()) {
            return response()->json([
                'success' => 0,
                'message' => trans('api::message.Error input data!'),
                'errors' => $validator->errors(),
            ]);
        }

        $hrmContractId = $params['hrm_contract_id'];
        $collectionModel = ContractModel::where('hrm_contract_id', $hrmContractId)->first();
        if (!$collectionModel) {
            return [
                'success' => 0,
                'message' => 'Không tìm thấy hợp đồng nào!'
            ];
        }

        DB::beginTransaction();
        try {            
            $collectionModel->delete();
            $collectionModel->saveHistory();
            DB::commit();
            return [
                'success' => 1,
                'data' => 'Xóa hợp đồng thành công!',
            ];
        } catch (\Exception $ex) {
            DB::rollBack();
            \Log::info($ex);
            return [
                'success' => 0,
                'message' => HrmCommonHelper::getInstance()->errorMessage($ex)
            ];
        }
    }

    public function createTeaching(Request $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->all();
            $valid = validator()->make($request->all(), [
                'title' => 'required|max:255',
                'scope' => 'required|in:1,2,3',
                'teams' => 'array',
                'detail_class_choose' => 'required|array',
                'course_type_id' => 'required|in:1,2',
                'content' => 'required',
                'target' => 'required',
                'employee_email' => 'required|exists:employees,email',
            ]);

            if ($valid->fails()) {
                return response()->json([
                    'success' => 0,
                    'message' => $valid->messages()
                ]);
            }
            //Check employee_email
            $emp = Employee::where('email', $request->get('employee_email'))->first(['id', 'name']);
            if (!$emp) {
                $validator = validator()->make([], []);
                $validator->after(function ($vld) {
                    $vld->errors()->add('employee_email', 'This email was not found.');
                });
                return response()->json([
                    'success' => 0,
                    'message' => $validator->messages()
                ]);
            }
            //Check validate team
            $vldTeam = [];
            $dataTeams = !empty($data['teams'][0]) ? $data['teams'] : [];
            if ($dataTeams) {
                $teams = Team::all()->pluck('id')->toArray();
                foreach ($dataTeams as $team) {
                    if (!in_array($team, $teams)) {
                        $vldTeam[] = 'Team '.$team. ' not found';
                    }
                }
            }
            if ($vldTeam) {
                $validator = validator()->make([], []);
                $validator->after(function ($vld) use ($vldTeam) {
                    $vld->errors()->add('teams', $vldTeam);
                });
                return response()->json([
                    'success' => 0,
                    'message' => $validator->messages()
                ]);
            }
            //Check validate time
            $vldName = [];
            $vldStartDate = [];
            $vldEndDate = [];
            $datatimes = $data['detail_class_choose'];
            $now = date('Y-m-d H:i');
            foreach ($datatimes as $time) {
                if (empty($time['name'])) {
                    $vldName[] = 'Name is required';
                }
                if (empty($time['start_date'])) {
                    $vldStartDate[] = 'start_date is required';
                }
                if (empty($time['end_date'])) {
                    $vldEndDate[] = 'end_date is required';
                }
                //regex name
                if (!preg_match('/^[0-9]*$/', $time['name'])) {
                    $vldName[] = 'Name must be a integer';
                }
                //regex time
                $pattern = "/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1]) ([0-1][0-9]|2[0-3]):([0-5][0-9])$/";
                if (!preg_match($pattern, $time['start_date'])) {
                    $vldStartDate[] = 'start_date is not in the correct format';
                }
                if (!preg_match($pattern, $time['end_date'])) {
                    $vldEndDate[] = 'start_date must be greater than the current time';
                }

                $now_date = strtotime($now);
                $start_date = strtotime($time['start_date']);
                if ($start_date < $now_date) {
                    $vldStartDate[] = 'start_date must be greater than the current time';
                }
                if (substr($time['start_date'], 0, 10) != substr($time['end_date'], 0, 10)) {
                    $vldStartDate[] = 'start_date must be equal to end_date';
                }
            }
            if ($vldName) {
                $validator = validator()->make([], []);
                $validator->after(function ($vld) use ($vldName) {
                    $vld->errors()->add('name', $vldName);
                });
                return response()->json([
                    'success' => 0,
                    'message' => $validator->messages()
                ]);
            }
            if ($vldStartDate) {
                $validator = validator()->make([], []);
                $validator->after(function ($vld) use ($vldStartDate) {
                    $vld->errors()->add('start_date', $vldStartDate);
                });
                return response()->json([
                    'success' => 0,
                    'message' => $validator->messages()
                ]);
            }
            if ($vldEndDate) {
                $validator = validator()->make([], []);
                $validator->after(function ($vld) use ($vldEndDate) {
                    $vld->errors()->add('end_date', $vldEndDate);
                });
                return response()->json([
                    'success' => 0,
                    'message' => $validator->messages()
                ]);
            }

            $hours = 0;
            foreach ($datatimes as $time) {
                $first_date = date_create($time['start_date']);
                $second_date = date_create($time['end_date']);
                $datediff = date_diff($first_date, $second_date);
                $hour = $datediff->format('%h');
                $hours = $hours + $hour;
            }
            if (!empty($data['teams'][0])) {
                $request['teams'] = implode(',', $request['teams']);
            }
            $request->merge([
                'employee_id' => $emp->id,
                'tranning_hour' => $hours,
                'status' => EducationTeacher::STATUS_NEW
            ]);
            $lastId = EducationTeacher::create($request->except(['detail_class_choose']))->id;
            foreach ($request->detail_class_choose as $value) {
                $value['education_teacher_id'] = $lastId;
                EducationTeacherTime::create($value);
            }

            $this->registerTeachingService->send($request, $lastId, true);
            DB::commit();

            return [
                'success' => 1,
                'data' => 'Đăng ký giảng dạy thành công!'
            ];
        } catch (\Exception $ex) {
            DB::rollback();
            Log::info($ex);
            return [
                'success' => 0,
                'message' => HrmCommonHelper::getInstance()->errorMessage($ex)
            ];
        }
    }

}