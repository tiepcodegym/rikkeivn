<?php

namespace Rikkei\Welfare\Model;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Rikkei\Core\Model\CoreModel;
use Rikkei\Notify\Classes\RkNotify;
use Rikkei\Team\Model\Team;
use Rikkei\Team\Model\TeamMember;
use Rikkei\Team\Model\Role;
use Rikkei\Team\Model\Employee;
use Rikkei\Welfare\Model\WelfareFee;
use Rikkei\Core\Model\EmailQueue;
use Carbon\Carbon;

class WelEmployee extends CoreModel
{
    const IS_CONFIRM = 1;
    const UN_CONFIRM = 0;
    const IS_JOINED = 1;
    const UN_JOINED = 0;

    protected $table = 'wel_employee';

    protected $fillable = ['wel_id', 'employee_id', 'is_confirm', 'is_joined', 'created_by'];

    /**
     * get grid data
     *
     * @return object
     */
    public static function getGridData($options)
    {
        $collection = DB::table('wel_employee')
            ->leftjoin('team_members','wel_employee.employee_id','=','team_members.employee_id')
            ->leftJoin('teams','team_members.team_id','=','teams.id')
            ->leftJoin('roles','team_members.role_id','=','roles.id')
            ->leftJoin('employees','wel_employee.employee_id','=','employees.id')
            ->leftJoin('wel_fee','wel_employee.wel_id','=','wel_fee.wel_id')
            ->leftjoin('welfares','welfares.id', '=','wel_employee.wel_id')
            ->where('wel_employee.wel_id',$options['wel_id'])
            ->select( 'employees.employee_code as empcCode',
                'employees.name as empname',
                DB::raw('group_concat(DISTINCT roles.role) as role'),
                DB::raw('group_concat(teams.name) as depname'),
                'wel_employee.is_confirm as confirm',
                'wel_employee.is_joined as joined',
                'wel_fee.empl_offical_fee as empFee',
                'wel_fee.empl_offical_company_fee as comFee',
                'wel_employee.wel_id as wel_id',
                'wel_employee.employee_id as employee_id',
                'welfares.end_at_exec',
                'welfares.end_at_register',
                'wel_employee.cost_employee',
                'wel_employee.cost_company',
                'welfares.start_at_register',
                'employees.id as empId',
                'employees.email'
            );

        if (!empty($options['emp_code'])) {
            $collection->where('employees.employee_code', 'like', '%'.trim($options['emp_code']).'%');
        }
        if (!empty($options['emp_name'])) {
            $collection->where('employees.name', 'like', '%'.trim($options['emp_name']).'%');
        }
        if (isset($options['is_confirm']) && is_numeric($options['is_confirm'])) {
            $collection->where('wel_employee.is_confirm', $options['is_confirm']);
        }
        if (isset($options['is_joined']) && is_numeric($options['is_joined'])) {
            $collection->where('wel_employee.is_joined', $options['is_joined']);
        }
        return $collection->groupBy('wel_employee.employee_id');
    }

    /**
     * Check Welfare exists in table
     *
     * @param int $welId
     * @return \phpDocumentor\Reflection\Types\Boolean
     */
    public static function checkWel($welId)
    {
        return self::where('wel_id', $welId)->count() ? true : false;
    }

    /**
     * Save information Employee join event
     *
     * @param array $teamIds
     * @param array $roleIds
     * @param int $welId
     */
    public static function saveEmployee($teamIds, $roleIds, $welId)
    {
        $teamPath = Team::getTeamPath();
        $teamIdSelect = [];
        foreach ($teamIds as $teamId) {
            $teamIdSelect[] = (int)$teamId;
            if (!isset($teamPath[$teamId]) ||
                !isset($teamPath[$teamId]['child'])
            ) {
                continue;
            }
            $teamIdSelect = array_merge($teamIdSelect, $teamPath[$teamId]['child']);
        }

        $teams = array_unique($teamIdSelect);

        $employees = TeamMember::whereIn('team_id', $teams)
            ->whereIn('role_id', $roleIds)
            ->lists('employee_id')
            ->toArray();

        $event = Event::find($welId);

        if (self::checkWel($welId)) {
            $event->welfareEmployee()->sync($employees);
        } else {
            $event->welfareEmployee()->attach($employees);
        }
    }

    /**
     * Confirmation of the employee is included in the welfare
     *
     * @param int $welId
     * @param int $emplId
     * @return WelEmployee $welEmployee
     */
    public static function getEmployeeInWelfare($welId, $emplId)
    {
        return self::where([
            ['wel_id', $welId],
            ['employee_id', $emplId],
        ])->first();
    }

    /**
     * Get list Email of Employee by Welfare Id
     *
     * @param int $welId
     * @return array
     */
    public static function getEmailEmplByWelId($welId)
    {
        return self::where('wel_id', $welId)
                ->join('employees', 'employees.id', '=', 'wel_employee.employee_id')
                ->lists('employees.email')
                ->toArray();
    }

    /**
     * Check Employee confirmed
     *
     * @param imy $welId
     * @param int $empId
     * @return boolean
     */
    public static function checkEmployeeConfirm($welId, $empId)
    {
        return self::where('wel_id', $welId)
                ->where('employee_id', $empId)
                ->where('is_confirm', self::IS_CONFIRM)
                ->count() ? true : false;
    }

    /**
     * Get nfomation Employee by employeeId
     *
     * @param array $emplIds
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getInfoEmployeeByEmplIds($emplIds)
    {
        $team_member = DB::table('team_members')
            ->join('teams', 'team_members.team_id', '=', 'teams.id')
            ->join('roles', 'team_members.role_id', '=', 'roles.id')
            ->select('team_members.employee_id', 'teams.name as depname', 'roles.role as role');

        $infoEmpl = DB::table('employees as e')
            ->join(DB::raw('(' . $team_member->toSql() . ') as i'), 'i.employee_id', '=', 'e.id')
            ->leftjoin('users', 'users.employee_id', '=', 'e.id')
            ->select('e.employee_code as empcCode', 'e.name as empname', 'i.role', 'e.mobile_phone', 'e.id', 'users.email')
            ->whereIn('e.id', $emplIds)
            ->groupBy('empcCode')
            ->get();
        return $infoEmpl;
    }

    /**
     * Option Employee
     *
     * @param int $welId
     * @param string $search
     * @return Array
     */
    public static function getOptionEmployee($welId, $search)
    {
        return self::join('employees', 'employees.id', '=', 'wel_employee.employee_id')
            ->select('wel_employee.employee_id as id', 'employees.name as name')
            ->where('wel_employee.wel_id', $welId)
            ->where('name','LIKE',"%$search%")
            ->get();
    }

    /**
     * get employee by event
     */
    public static function getAllEmployeesOfTeam($teamId, $event, array $where = []) {
        $teamPath = Team::getTeamPath();
        $teams[] = $teamId;
        if($teamId != 1) {
            if (isset($teamPath[$teamId]['child'])) {
                $teams = $teamPath[$teamId]['child'];
            }
        }
        $tableSelf = self::getTableName();
        $enventSql = self::select('wel_id','employee_id')
            ->where('wel_id',$event);

        $enventSql = "select `wel_id`, `employee_id` from `{$tableSelf}` where `wel_id` = '{$event}'";
        $tableEmployee = Employee::getTableName();
        $tableTeamEmployee = TeamMember::getTableName();
        $tableRole = Role::getTableName();

        $collection = Employee::select("{$tableEmployee}.id", "{$tableEmployee}.email", "{$tableEmployee}.name",
            "{$tableRole}.role", "{$tableEmployee}.employee_code", "{$tableEmployee}.mobile_phone", "tblE.wel_id")
            ->whereNull("{$tableEmployee}.leave_date")
            ->join($tableTeamEmployee, "{$tableTeamEmployee}.employee_id", '=', "{$tableEmployee}.id")
            ->leftJoin($tableRole, "{$tableTeamEmployee}.role_id", '=', "{$tableRole}.id")
            ->leftJoin(DB::raw ('('. $enventSql .') as tblE'), "{$tableEmployee}.id", '=', 'tblE.employee_id')
            ->whereIn("{$tableTeamEmployee}.team_id", $teams)
            ->groupBy("{$tableEmployee}.id");
        if (isset($where['gender'])) {
            $collection->where('gender', $where['gender']);
        }
        return $collection;
    }

     /*
     * @param int $wel_id
     * @param aray $arg
     * @return array
     */
    public static function listExportEmployee($wel_id, $arg = [])
    {
        $dateOfficial = WelfareFee::getDateOfficialEmployee($wel_id);
        $date = $dateOfficial->empl_offical_after_date;
        $check = false;

        if (isset($date) && $date != null) {
            $check = true;
            $endDate = new Carbon($date);
            $trialEmpl = DB::table('wel_employee')
                ->leftJoin('employees','wel_employee.employee_id','=','employees.id')
                ->where(function($query) {
                    $query->orWhereNull('employees.leave_date')
                        ->orWhereDate('employees.leave_date', '>=', Carbon::now()->format('Y-m-d'));
                })
                ->leftJoin('wel_fee','wel_employee.wel_id','=','wel_fee.wel_id')
                ->leftjoin('welfares','welfares.id', '=','wel_employee.wel_id')
                ->where('wel_employee.wel_id', $wel_id)
                ->where(function ($query) use ($endDate) {
                    $query->whereNull('employees.offcial_date')
                        ->orWhere('employees.offcial_date', '>', $endDate->format('Y-m-d 23:59:59'));
                })
                ->select( 'wel_employee.employee_id as employee_id',
                    'wel_fee.empl_trial_fee as empl_fee',
                    'employees.id_card_number',
                    'employees.birthday',
                    'wel_fee.empl_trial_company_fee as empl_company_fee')
                ->groupBy('wel_employee.employee_id');
            $trialEmplWel = $trialEmpl->get();
        }

        $listEmpl = self::infoEmployeeWithFeeActual($wel_id)->get();
        $emplwelfare = [];
        foreach ($listEmpl as $item) {
            $emplwelfare[$item->employee_id] = [
                'emplCode' => $item->empcCode,
                'emplName' => $item->empname,
                'id_card_number' => $item->id_card_number,
                'birthday' => $item->birthday,
                'role' => $item->role,
                'depname' => $item->depname,
                'empl_fee' => (int) $item->empFee,
                'empl_company_fee' => (int) $item->comFee,
            ];
        }

        if (isset($arg['is_confirm'])) {
            foreach ($listEmpl as $item) {
                $emplwelfare[$item->employee_id]['is_confirm'] = ($item->confirm == self::IS_CONFIRM) ?
                                                                Lang::get('welfare::view.Yes') : Lang::get('welfare::view.Not');
            }
        }
        if (isset($arg['is_joined'])) {
            foreach ($listEmpl as $item) {
                $emplwelfare[$item->employee_id]['is_confirm'] = ($item->confirm == self::IS_CONFIRM) ?
                                                                Lang::get('welfare::view.Yes') : Lang::get('welfare::view.Not');
                $emplwelfare[$item->employee_id]['is_joined'] = ($item->joined == self::IS_JOINED) ?
                                                                Lang::get('welfare::view.Yes') : Lang::get('welfare::view.Not');
                $emplwelfare[$item->employee_id]['beneficiaries'] = Lang::get('welfare::view.Yes');
            }
        }
        if ($check && isset($trialEmplWel)) {
            foreach ($trialEmplWel as $value) {
                if (isset($emplwelfare[$value->employee_id])) {
                    $emplwelfare[$value->employee_id]['empl_fee'] = (int) $value->empl_fee;
                    $emplwelfare[$value->employee_id]['empl_company_fee'] = (int) $value->empl_company_fee;
                }
            }
        }
        return array_reverse($emplwelfare);

    }

    /**
     *
     */
    public static function checkRelativeAttachByWelId($welId) {
        return self::where('wel_id', $welId)
            ->count() ? true : false;
    }

    /**
     * get employee by team
     * @return array id employee
     */
    /**
     * get employee by event
     */
    public static function getAllIdEmployeesOfTeam($teamId, array $where = []) {
        $teamPath = Team::getTeamPath();
        $teams[] = $teamId;
        if($teamId != 1) {
            if (isset($teamPath[$teamId]['child'])) {
                $teams = $teamPath[$teamId]['child'];
            }
        }
        $tableEmployee = Employee::getTableName();
        $tableTeamEmployee = TeamMember::getTableName();
        $collection = Employee::select('id')
            ->join($tableTeamEmployee, $tableTeamEmployee.'.employee_id',
                '=', $tableEmployee.'.id')
            ->whereNull($tableEmployee.'.leave_date')
            ->whereIn($tableTeamEmployee.'.team_id', $teams);
        if (isset($where['gender'])) {
            $collection->where('gender', $where['gender']);
        }
        return $collection->distinct()->pluck('id')->toArray();
    }

    /**
     * get cost employee and company join
     * @param id employee, id event
     */
    public static function getCostEvent($idEmp,$idEvent) {
        return  self::select('cost_employee','cost_company')
                    ->where('employee_id',$idEmp)
                    ->where('wel_id',$idEvent)
                    ->where('is_confirm',self::IS_CONFIRM)->first();
    }

    /**
     * update cost employee
     */
    public static function updateCostEvent($request,$emFee,$comFee) {
        $dataCostEmployee = self::where('wel_id',$request->event)
            ->where('employee_id',$request->idEmployee);
        DB::beginTransaction();
        try {
            if ($dataCostEmployee) {
                $dataCostEmployee->update(['cost_employee'=>$emFee,'cost_company'=>$comFee]);
            }
            DB::commit();
            return true;
        } catch(Exception $ex) {
            DB::rollback();
            throw $ex;
            return false;
        }
    }

    /**
     * Option Employee
     *
     * @param int $welId
     * @return Array
     */
    public static function getIdEmployee($welId) {
        return self::join('employees', 'employees.id', '=', 'wel_employee.employee_id')
            ->select('wel_employee.employee_id as id', 'employees.name as name')
            ->where('wel_employee.wel_id', $welId)
            ->pluck('id')
            ->toArray();
    }

    /**
     * @param type $id
     * @return type
     */
    public static function infoEmployeeWithFeeActual($id)
    {
        return DB::table('wel_employee')
            ->leftjoin('team_members', 'wel_employee.employee_id', '=', 'team_members.employee_id')
            ->leftJoin('teams', 'team_members.team_id', '=', 'teams.id')
            ->leftJoin('roles', 'team_members.role_id', '=', 'roles.id')
            ->leftJoin('employees', 'wel_employee.employee_id', '=','employees.id')
            ->leftJoin('wel_fee', 'wel_employee.wel_id', '=', 'wel_fee.wel_id')
            ->leftjoin('welfares', 'welfares.id', '=', 'wel_employee.wel_id')
            ->where('wel_employee.wel_id', $id)
            ->select('employees.employee_code as empcCode',
                'employees.name as empname',
                'employees.id_card_number',
                'employees.birthday',
                DB::raw('group_concat(DISTINCT roles.role) as role'),
                DB::raw('group_concat(teams.name) as depname'),
                'wel_employee.is_confirm as confirm',
                'wel_employee.is_joined as joined',
                'wel_fee.empl_offical_fee as empFee',
                'wel_fee.empl_offical_company_fee as comFee',
                'wel_employee.wel_id as wel_id',
                'wel_employee.employee_id as employee_id',
                'welfares.end_at_exec',
                'welfares.end_at_register',
                'wel_employee.cost_employee',
                'wel_employee.cost_company')
            ->groupBy('wel_employee.employee_id');
    }

    /**
     * update cost event employee when join
     */
    public static function updateCostEmployeeJoin($data,$cost) {
        $record = WelEmployee::where('employee_id', $data['employee_id'])
                ->select('offcial_date','empl_offical_after_date','cost_employee',
                        'cost_company')
                ->where('wel_employee.wel_id', $data['wel_id'])
                ->leftjoin('employees','wel_employee.employee_id','=','employees.id')
                ->leftjoin('wel_fee','wel_fee.wel_id','=','wel_employee.wel_id')
                ->first();
        if($data['name'] == "is_confirm") {
            if($data['value'] == self::IS_JOINED) {
                if($record->offcial_date > $record->empl_offical_after_date
                   && $record->empl_offical_after_date != null) {
                    $emFee = $cost['empl_trial_fee'];
                    $comFee = $cost['empl_trial_company_fee'];
                }
                else {
                    $emFee = $cost['empl_offical_fee'];
                    $comFee = $cost['empl_offical_company_fee'];
                }
            } else {
                $emFee = 0;
                $comFee = 0;
            }
        } else {
            $emFee = $record->cost_employee;
            $comFee = $record->cost_company;
        }

        /**
         * send mail confirm or unconfirmed join event
         */
        $employee = Employee::find($data['employee_id']);
        $template = 'welfare::template.mail_confirm';
        $subject = trans('welfare::view.You had confirm join event');
        if ($data['value'] == self::UN_CONFIRM) {
            $subject = trans('welfare::view.You had unconfirmed join event');
        }
        $dataMail = [
            'email' => $employee->email,
            'receiver_name' => $employee->name,
            'title' => $subject,
            'link' => route("welfare::welfare.confirm.welfare", ['id' => $data['wel_id']]),
        ];
        DB::beginTransaction();
        try {
            WelEmployee::where('employee_id', $data['employee_id'])
                ->select('is_confirm','cost_employee','cost_company','offcial_date','empl_offical_after_date')
                ->where('wel_employee.wel_id', $data['wel_id'])
                ->update([$data['name'] => $data['value'],
                        'cost_employee' => $emFee,
                        'cost_company' => $comFee,
                        ]);
            $emailQueue = new EmailQueue();
            $emailQueue->setTo($dataMail['email'])
                ->setSubject($subject)
                ->setTemplate($template, $dataMail)
                ->setNotify(
                    $employee->id,
                    $subject,
                    $dataMail['link'], ['category_id' => RkNotify::CATEGORY_ADMIN]
                )
                ->save();
            DB::commit();
            return true;
        } catch(Exception $ex) {
            throw $ex;
            DB::rollback();
            return false;
        }
    }

    /**
     * up date employee join
     */
    public static function updateEmployeeJoined($data) {
        DB::beginTransaction();
        try {
            WelEmployee::where('wel_id',$data['id'])->update(['is_joined'=> self::IS_JOINED]);
            DB::commit();
            return true;
        } catch(Exception $ex) {
            throw $ex;
            DB::rollback();
            return false;
        }
    }

    /**
     * Get label confirm
     *
     * @return array
     */
    public static function getLabelConfirm()
    {
        return [
            self::IS_CONFIRM => trans('welfare::view.Confirm'),
            self::UN_CONFIRM => trans('welfare::view.Unconfirmed'),
        ];
    }

    /**
     * Get label join
     *
     * @return array
     */
    public static function getLabelJoined()
    {
        return [
            self::IS_JOINED => trans('welfare::view.Joined'),
            self::UN_JOINED => trans('welfare::view.UnJoined'),
        ];
    }

    /**
     * Get employee unconfirm event
     *
     * @param int $welId
     */
    public static function getEmpUnConfirm($welId)
    {
        return self::where('wel_id', $welId)
            ->where('is_confirm', self::UN_CONFIRM)
            ->join('employees', 'employees.id', '=', 'wel_employee.employee_id')
            ->lists('employees.email')
            ->toArray();
    }
}
