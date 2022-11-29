<?php

namespace Rikkei\Api\Http\Controllers\Employee;

use Rikkei\Core\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Rikkei\Api\Helper\Employee as EmployeeHelper;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Validator;
use Rikkei\Project\Model\Project;
use Rikkei\Resource\View\getOptions;
use Rikkei\Team\Model\Team;
use Rikkei\Team\Model\Employee;
use Exception;
use Rikkei\Resource\View\View;
use Rikkei\Team\View\EmpLib;
use Rikkei\Team\Model\Role;
use Carbon\Carbon;

class EmployeeController extends Controller
{
    /**
     * get total employees
     * @params: date, team_id, include_child_team
     */
    public function getTotal(Request $request)
    {
        if (isset($request->date)) {
            $valid = Validator::make($request->all(), ['date' => 'date_format:Y-m-d']);
            if ($valid->fails()) {
                return [
                    'success' => 0,
                    'total' => Lang::get('api::message.Date is incorrect'),
                ];
            }
        }

        try {
            $total = EmployeeHelper::getInstance()->getTotal($request->all());
            return [
                'success' => 1,
                'total' => $total,
            ];
        } catch (\Exception $ex) {
            \Log::info($ex);
            return [
                'success' => 0,
                'message' => EmployeeHelper::getInstance()->errorMessage($ex)
            ];
        }
    }

    /**
     * get total employees
     * @params: employee_id, fields
     */
    public function getInfo(Request $request)
    {
        try {
            $info = EmployeeHelper::getInstance()->getInfo($request->all());
            return [
                'success' => 1,
                'data' => $info,
            ];
        } catch (\Exception $ex) {
            \Log::info($ex);
            return [
                'success' => 0,
                'message' => EmployeeHelper::getInstance()->errorMessage($ex)
            ];
        }
    }

    /**
     * get all skills of all employees
     */
    public function getSkills()
    {
        try {
            return [
                'success' => 1,
                'data' => EmployeeHelper::getInstance()->getSkills(),
            ];
        } catch (\Exception $ex) {
            \Log::info($ex);
            return [
                'success' => 0,
                'message' => EmployeeHelper::getInstance()->errorMessage($ex)
            ];
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getList(Request $request)
    {
        $rules = [
            'project_ids' => 'array',
            'status' => 'integer',
            'month' => 'date_format:Y-m',
            'branch' => 'string',
        ];
        $messages = [
            'project_ids.array' => Lang::get('api::message.Project_id must be an array!'),
            'status.integer' => Lang::get('api::message.Status must be an integer'),

        ];
        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'success' => 0,
                'message' => trans('api::message.Error input data!'),
                'errors' => $validator->errors(),
            ]);
        }
        try {
            return response()->json([
                'success' => 1,
                'data' => EmployeeHelper::getInstance()->getListEmp($request->all()),
            ]);

        } catch (\Exception $ex) {
            \Log::info($ex);
            return response()->json([
                'success' => 0,
                'data' => EmployeeHelper::getInstance()->errorMessage($ex),
            ]);
        }
    }

    /**
     * get infor employees
     * @params: email
     */
    public function getInfoFull(Request $request)
    {
        try {
            $info = EmployeeHelper::getInstance()->getInfoFull($request->email);
            return [
                'success' => 1,
                'data' => $info,
            ];
        } catch (\Exception $ex) {
            \Log::info($ex);
            return [
                'success' => 0,
                'message' => EmployeeHelper::getInstance()->errorMessage($ex)
            ];
        }
    }

    /**
     * get infor employees
     * @params: emails
     */
    public function getInfoFullList(Request $request)
    {
        try {
            $info = EmployeeHelper::getInstance()->getInfoFullList($request->emails);
            return [
                'success' => 1,
                'data' => $info,
            ];
        } catch (\Exception $ex) {
            \Log::info($ex);
            return [
                'success' => 0,
                'message' => EmployeeHelper::getInstance()->errorMessage($ex)
            ];
        }
    }

    /**
     * list periods of employees onsite in Japan
     * @param Request $request
     * @return array
     */
    public function listEmployeesOnsiteJapan(Request $request)
    {
        $params = $request->all();
        $rules = [
            'month_from' => 'required|date_format:Y-m',
            'month_to' => 'required|date_format:Y-m',
        ];
        if (isset($params['team_id']) && is_array($params['team_id'])) {
            $rules['team_id'] = 'array:teams, id';
        } else {
            $rules['team_id'] = 'numeric';
        }
        $validator = Validator::make($params, $rules);
        if ($validator->fails()) {
            return response()->json([
                'success' => 0,
                'message' => trans('api::message.Error input data!'),
                'errors' => $validator->errors(),
            ]);
        }
        try {
            $data = EmployeeHelper::getInstance()->listEmployeesOnsiteJapan($params);
            return [
                'success' => 1,
                'data' => $data,
            ];
        } catch (\Exception $ex) {
            \Log::info($ex);
            return [
                'success' => 0,
                'message' => EmployeeHelper::getInstance()->errorMessage($ex)
            ];
        }
    }

    /**
     * list employee Viet Nam division onsite Japan
     * @return array
     */
    public function listEmployeesOnsiteVietNam(Request $request)
    {
        $rules = [
            'month_from' => 'date_format:Y-m',
            'month_to' => 'date_format:Y-m'
        ];
        $messages = [

        ];
        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            return response()->json([
                'success' => 0,
                'message' => trans('api::message.Error input data!'),
                'errors' => $validator->errors(),
            ]);
        }
        try {
            $data = EmployeeHelper::getInstance()->listEmployeesOnsiteVietNam($request->all());
            return [
                'success' => 1,
                'data' => $data,
            ];
        } catch (\Exception $ex) {
            \Log::info($ex);
            return [
                'success' => 0,
                'message' => EmployeeHelper::getInstance()->errorMessage($ex)
            ];
        }
    }

    /**
     * list of onsite employees for the month
     * @param Request $request
     * @return array
     */
    public function listEmployeesOnsiteInMonth(Request $request)
    {
        $params = $request->all();
        $rules = [
            'month' => 'required|in:01,1,02,2,03,3,04,4,05,5,06,6,07,7,08,8,09,9,10,11,12',
            'year' => 'required|digits:4|integer|min:1900|max:'.(date('Y')+100),
        ];
        $validator = Validator::make($params, $rules);
        if ($validator->fails()) {
            return response()->json([
                'success' => 0,
                'message' => trans('api::message.Error input data!'),
                'errors' => $validator->errors(),
            ]);
        }
        try {
            $data = EmployeeHelper::getInstance()->listEmployeesOnsiteInMonth($params);
            return [
                'success' => 1,
                'data' => $data,
            ];
        } catch (\Exception $ex) {
            \Log::info($ex);
            return [
                'success' => 0,
                'message' => EmployeeHelper::getInstance()->errorMessage($ex)
            ];
        }
    }

    /**
     * list of onsite employees for the month
     * @param Request $request
     * @return array
     */
    public function utilization(Request $request)
    {
        $params = $request->all();
        $statusOptions = Project::lablelState();
        $rules = [
            'projId' => 'exists:projs,id',
            'projStatus' => 'in:'.implode(",", array_keys($statusOptions)),
            // 'startDate' => 'date',
            // 'endDate' => 'date',
            'viewMode' => 'required|in:day',
            'effort' => 'in:'.implode(",", array_keys(getOptions::getEffortPeriodOptions())),
            'limit' => 'integer|min:1',
            'page' => 'integer|min:1',
            'empIds' => 'array',
            'programs' => 'array',
            'teamId' => 'array',
            'teamId.*' => 'exists:teams,id',
            'updated_from' => 'date',
            'updated_to' => 'date'
        ];      

        $validator = Validator::make($params, $rules);
        if ($validator->fails()) {
            return response()->json([
                'success' => 0,
                'message' => trans('api::message.Error input data!'),
                'errors' => $validator->errors(),
            ]);
        }
        $updated_from = $request->updated_from;
        $updated_to = $request->updated_to;
        if ($updated_from && $updated_to && ($updated_to < $updated_from)) {
            $validator = validator()->make([], []);
            $validator->after(function ($vld) {
                $vld->errors()->add('updated_to', 'The updated to must be a date after or equal updated from.');
            });
            return response()->json([
                'success' => 0,
                'message' => $validator->messages()
            ]);
        }
        try {
            $data = EmployeeHelper::getInstance()->utilization($params);
            return [
                'success' => 1,
                'data' => $data,
            ];
        } catch (\Exception $ex) {
            \Log::info($ex);
            return [
                'success' => 0,
                'message' => EmployeeHelper::getInstance()->errorMessage($ex)
            ];
        }
    }

    public function updateResignation(Request $request) {
        $params = $request->all();
        $rules = [
            'employee_email' => 'required|email',
            'leave_date' => 'required|date',
        ];      

        $validator = Validator::make($params, $rules);
        if ($validator->fails()) {
            return response()->json([
                'success' => 0,
                'message' => trans('api::message.Error input data!'),
                'errors' => $validator->errors(),
            ]);
        }
        try {
            $data = EmployeeHelper::getInstance()->updateResignation($params);
            return [
                'success' => 1,
                'data' => $data,
            ];
        } catch (\Exception $ex) {
            \Log::info($ex);
            return [
                'success' => 0,
                'message' => EmployeeHelper::getInstance()->errorMessage($ex)
            ];
        }
    }

    public function createEmployee(Request $request) {
        try {
            $data = $request->all();
            $empDeleted = null;
            if (isset($data['is_old_employee']) && $data['is_old_employee'] == 1) {
                $empDeleted = Employee::getLeaverByEmail($data['email']);
                if (!$empDeleted) {
                    throw new Exception('Employee not found');
                }
            }
            $cddEmpId = !empty($empDeleted) ? $empDeleted->id : null;
            $folkOption = EmpLib::getInstance()->folk();
            $folkOption = implode(',', array_keys($folkOption));

            $religionOption = EmpLib::getInstance()->relig();
            $religionOption = implode(',', array_keys($religionOption));

            $maritalOption = Employee::labelMarital();
            $maritalOption = implode(',', array_keys($maritalOption));

            $libCountry = View::getListCountries();
            $libCountry = implode(',', array_keys($libCountry));
            $rules = [
                'is_old_employee' => 'in:0,1',
                'status' => 'required|in:'.getOptions::CONTACTING,
                'name' => 'required|max:45',
                'email' => 'required|email|unique:employees,email'.($cddEmpId ? ',' . $cddEmpId : ',NULL').',id,deleted_at,NULL|regex:/@rikkeisoft.com$/',
                'gender' => 'required|in:'.Employee::GENDER_MALE.','.Employee::GENDER_FEMALE,
                'employee_code' => 'required|max:20',
                'birthday' => 'date_format:Y-m-d',

                'id_card_number' => 'string|max:255',
                'id_card_date' => 'date_format:Y-m-d',
                'id_card_place' => 'string|max:255',

                'passport_number' => 'string|max:50',
                'passport_date_start' => 'date_format:Y-m-d',
                'passport_date_exprie' => 'date_format:Y-m-d',
                'passport_addr' => 'string|max:255',
                'japanese_name' => 'string|max:255',

                'folk' => 'string|max:255|in:'.$folkOption,
                'religion' => 'string|max:255|in:'.$religionOption,
                'marital' => 'string|max:255|in:'.$maritalOption,
                'country' => 'in:'.$libCountry
            ];
            if (isset($data['is_old_employee']) && $data['is_old_employee'] == 1) {
                $rules['employee_code'] = 'max:20';
            }
            $valid = validator()->make($request->all(), $rules);            
            if ($valid->fails()) {
                return response()->json([
                    'success' => 0,
                    'message' => $valid->messages()
                ]);
            }

            //Validate team
            $teamPositions = $data['team'];
            if (!$teamPositions) {
                throw new Exception('Employee must belong to at least one team.');
            }
            if (!is_array($teamPositions)) {
                throw new Exception('team must be an array.');
            }
            $optionPositionsIds = Role::where('special_flg', Role::FLAG_POSITION)->pluck('id')->toArray();
    
            $vldTeamId = [];
            $vldPosition = [];
            $vldIsWorking = [];
            $vldEndAt = [];
            $countIsWoking = 0;
            $dateNow = Carbon::now();
            foreach ($teamPositions as $key => $team) {
                if (!empty($team['is_working']) && $team['is_working'] == 1) {
                    $countIsWoking++;
                }
                if (empty($team['team_id'])) {
                    $vldTeamId[] = 'Team ['.$key.'] team_id is required.';
                    continue;
                }
                if (empty($team['position'])) {
                    $vldPosition[] = 'Team ['.$key.'] position is required.';
                    continue;
                }
                if (empty($team['start_at'])) {
                    $vldPosition[] = 'Team ['.$key.'] start_at is required.';
                    continue;
                }
    
                $dtTeam = Team::find($team['team_id']);
                if (!$dtTeam) {
                    $vldTeamId[] = 'The selected team_id ['.$key.'] is invalid.';
                    continue;
                }
                if (!in_array($team['position'], $optionPositionsIds)) {
                    $vldPosition[] = 'The postion ['.$key.'] is invalid.';
                    continue;
                }
                if (!empty($team['is_working'])) {
                    if ($team['is_working'] == 1 && !empty($team['end_at']) &&  $team['end_at'] <= $dateNow->format("Y-m-d")) {
                        $vldEndAt[] = 'The current working team must have end_date after the current date.';
                        continue;
                    }
                    if (!in_array($team['is_working'], [0, 1])) {
                        $vldIsWorking[] = 'The is_working ['.$key.'] is invalid.';
                        continue;
                    }
                }
                if (!empty($team['end_at']) && $team['end_at'] <= $team['start_at']) {
                    $vldEndAt[] = 'The end date  ['.$key.'] at must be after start date.';
                    continue;
                }
            }
            if ($countIsWoking != 1) {
                return $this->showErrorValidate('You need to choose only a team with a working status', 'is_working');
            }
            if ($vldTeamId) {
                return $this->showErrorValidate($vldTeamId, 'team_id');
            }
            if ($vldPosition) {
                return $this->showErrorValidate($vldPosition, 'position');
            }
            if ($vldIsWorking) {
                return $this->showErrorValidate($vldIsWorking, 'is_working');
            }
            if ($vldEndAt) {
                return $this->showErrorValidate($vldEndAt, 'end_at');
            }

            $data = EmployeeHelper::getInstance()->createEmployee($data, $empDeleted);
            return [
                'success' => 1,
                'data' => $data,
            ];
        } catch (\Exception $ex) {
            \Log::info($ex);
            return [
                'success' => 0,
                'message' => EmployeeHelper::getInstance()->errorMessage($ex)
            ];
        }
    }

    public function showErrorValidate($dataInput, $nameInput)
    {
        $validator = validator()->make([], []);
        $validator->after(function ($vld) use ($dataInput, $nameInput) {
            $vld->errors()->add($nameInput, $dataInput);
        });
        return response()->json([
            'success' => 0,
            'message' => $validator->messages()
        ]);
    }

    public function getEmpNotInProject(Request $request)
    {
        try {
            $info = EmployeeHelper::getInstance()->getEmpNotInProject($request->time);
            return [
                'success' => 1,
                'data' => $info,
            ];
        } catch (\Exception $ex) {
            \Log::info($ex);
            return [
                'success' => 0,
                'message' => EmployeeHelper::getInstance()->errorMessage($ex)
            ];
        }
    }

    public function getEmployeeRole(Request $request)
    {
        $params = $request->all();
        $rules = [
            'role_name' => 'required|array',
            'updated_from' => 'date_format:Y-m-d',
        ];      
        $validator = Validator::make($params, $rules);
        if ($validator->fails()) {
            return response()->json([
                'success' => 0,
                'message' => trans('api::message.Error input data!'),
                'errors' => $validator->errors(),
            ]);
        }
        
        $roleArrayTemp = Role::all()->pluck('role')->toArray();
        $roleArray = [];
        foreach ($roleArrayTemp as $role){
            $roleArray[] =  mb_strtolower(trim($role), 'UTF-8');
        }

        $err = false;
        $dataRoles = $params['role_name'];
        $checkRole = [];
        foreach ($dataRoles as $value) {
            $value = mb_strtolower(trim($value), 'UTF-8');
            if (empty($value)) {
                $err = true;
            }
            if (in_array($value, $roleArray)) {
                $checkRole[] = $value;
            }
        }
        if (empty($checkRole)) {
            $validator = validator()->make([], []);
            $validator->after(function ($vld)  {
            $vld->errors()->add('role_name', 'This role name was not exists.');
            });
            return response()->json([
                'success' => 0,
                'message' => $validator->messages(),
            ]);
        }
        $params['role_name'] = $checkRole;

        if ($err) {
            $validator = validator()->make([], []);
            $validator->after(function ($vld)  {
                $vld->errors()->add('role_name', 'Role name is required.');
            });
            return response()->json([
                'success' => 0,
                'message' => $validator->messages()
            ]);
        }

        try {
            $data = EmployeeHelper::getInstance()->getEmployeeRole($params);
            return [
                'success' => 1,
                'data' => $data,
            ];
        } catch (\Exception $ex) {
            \Log::info($ex);
            return [
                'success' => 0,
                'message' => EmployeeHelper::getInstance()->errorMessage($ex)
            ];
        }
    }

    public function getTeamsLeader(Request $request)
    {
        $data = $request->all();
        if(isset($data['branch'])){
            $rules = [
                'branch' => 'array',
            ];      
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return response()->json([
                    'success' => 0,
                    'message' => trans('api::message.Error input data!'),
                ]);
            }
        }
        try {
            $data = EmployeeHelper::getInstance()->getTeamsLeader($data);
            return [
                'success' => 1,
                'total' =>  count($data),
                'data' => $data,
            ];
        } catch (\Exception $ex) {
            \Log::info($ex);
            return [
                'success' => 0,
                'message' => EmployeeHelper::getInstance()->errorMessage($ex)
            ];
        }

    }

}
