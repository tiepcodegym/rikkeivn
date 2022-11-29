<?php

namespace Rikkei\Test\Http\Controllers;

use Rikkei\Core\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Rikkei\Core\View\Breadcrumb;
use Rikkei\Test\Models\Test;
use Rikkei\Team\View\TeamList;
use Rikkei\Team\Model\Team;
use URL;
use Rikkei\Test\Models\Type;
use Rikkei\Team\Model\Employee;
use Rikkei\Core\Model\User;
use Rikkei\Test\Models\Assignee;
use DB;
use Rikkei\Core\Model\CoreModel;
use Carbon\Carbon;
use Validator;
use Lang;

class AssigneeController extends Controller
{

    /**
     * exam index
     * @return type
     */
    public function index(Request $request)
    {
        Breadcrumb::add(trans('test::test.Exam list'));
        $response = array();
        $data = $request->all();
        if (isset($data)) {
            if (!empty($data)) {
                if (!isset($data['productsEmployee'])) {
                    if (isset($data['checkArr'])) {
                        $checkArr = $data['checkArr'];
                    } else {
                        $checkArr = null;
                    }
                    $testOption = Test::select('id', 'name')->get();
                    $response['content'] = view('test::manage.test.exam-list', [
                        'collectionEmployee' => Employee::getEmployeesByGridData($data['team'], $data['name'], $data['email']),
                        'collectionModel' => Test::getGridData(),
                        'types' => Type::getList(),
                        'teamsOption' => TeamList::toOption(null, true, false),
                        'view' => 'index',
                        'checkArr' => $checkArr,
                        'page' => 1,
                        'testOption' => $testOption
                    ])->render();
                    $response['success'] = 1;
                    return response()->json($response);
                }
                if (isset($data['productsEmployee'])) {
                    if (isset($data['checkArr'])) {
                        $checkArr = $data['checkArr'];
                    } else {
                        $checkArr = null;
                    }
                    $testOption = Test::select('id', 'name')->get();
                    return view('test::manage.test.exam-list', [
                        'collectionEmployee' => Employee::getEmployeesByGridData($data['team'], $data['name'], $data['email']),
                        'collectionModel' => Test::getGridData(),
                        'types' => Type::getList(),
                        'teamsOption' => TeamList::toOption(null, true, false),
                        'view' => 'index',
                        'checkArr' => $checkArr,
                        'page' => $data['productsEmployee'],
                        'testOption' => $testOption
                    ]);
                }
            }
        }
        $testOption = Test::select('id', 'name')->get();
        return view('test::manage.test.exam-list', [
            'collectionEmployee' => Employee::getEmployeesByGridData(),
            'collectionModel' => Test::getGridData(),
            'types' => Type::getList(),
            'teamsOption' => TeamList::toOption(null, true, false),
            'view' => 'index',
            'testOption' => $testOption
        ]);
    }

    /**
     * select division
     * @param Request $request
     * @return type
     */
    public function selectOption(Request $request)
    {
        $response = array();
        $data = $request->all();
        if (isset($data['division'])) {
            $teamName = Team::select('id', 'name')->whereIn('id', $data['division'])->get();
            $response['success'] = 1;
            $response['division'] = view('test::manage.includes.select-division', [
                'teamName' => $teamName
            ])->render();
        }
        if (isset($data['test'])) {
            $testOption = Assignee::getAssigneeByTestId($data['test']);
            $multiTimes = Test::where('id', $data['test'])->first();
            if (count($testOption) > 0) {
                $allTeams = [];
                $allEmployees = [];
                $timeFrom = '';
                $timeTo = '';
                foreach ($testOption as $value) {
                    if ($value->team_id) {
                        $allTeams[] = $value->team_id;
                    }
                    if ($value->employee_id) {
                        $allEmployees[] = $value->employee_id;
                    }
                    $timeFrom = $value->time_from;
                    $timeTo = $value->time_to;
                }
                $teamName = Team::select('id', 'name')->whereIn('id', $allTeams)->get();
                $response['multi_times'] = (int)$multiTimes->multi_times;
                $response['time_from'] = $timeFrom;
                $response['time_to'] = $timeTo;
                $response['checkDivision'] = $allTeams;
                $response['division'] = view('test::manage.includes.select-division', [
                    'teamName' => $teamName
                ])->render();
                $response['checkEmployee'] = $allEmployees;
                $collectionEmployee = Employee::getEmployeesById($allEmployees);
                $response['employee'] = view('test::manage.includes.select-employee', [
                    'collectionEmployee' => $collectionEmployee,
                    'view' => 'check'
                ])->render();
                $response['success'] = 1;
            } else {
                $response['error'] = 1;
                return $response;
            }
        }

        return response()->json($response);
    }

    /**
     * select employee
     * @param Request $request
     * @return type
     */
    public function selectEmployee(Request $request)
    {
        $response = array();
        $data = $request->all();
        if (isset($data)) {
            if (isset($data['checkArr'])) {
                $ids = $data['checkArr'];
            } else {
                $ids = null;
            }
            $collectionEmployee = Employee::getEmployeesById($ids);
            $response['content'] = view('test::manage.includes.select-employee', [
                    'collectionEmployee' => $collectionEmployee,
                    'view' => 'check'
                ])->render();
            $response['success'] = 1;
            return response()->json($response);
        }
        $response['error'] = 1;
        return response()->json($response);
    }

    /**
     * save exam list
     * @param Request $request
     * @return type
     */
    public function saveAssignee(Request $request) {
        $response = array();
        $data = $request->all();
        if (isset($data['ok'])) {
            $data = $data['data'];
            $this->save($data);
            $updateMultiTimes = Test::where('id', $data['test_id'])->first();
            $updateMultiTimes->multi_times = $data['multi_times'];
            $updateMultiTimes->save();
            $response['success'] = 1;
            $response['message'] = Lang::get('core::message.Save success');
            return response()->json($response);
        }
        if (!isset($data['data'])) {
            $response['error'] = 1;
            $response['message'] = trans('test::test.No test selected');
            return $response;
        } else {
            if (empty($data['data'])) {
                $response['error'] = 1;
                $response['message'] = trans('test::test.No test selected');
                return $response;
            }
            $data = $data['data'];
        }
        if (empty($data['test_id'])) {
            $response['error'] = 1;
            $response['message'] = trans('test::test.No test selected');
            return $response;
        } else {
            $currentTest = Assignee::getAssigneeByTestId($data['test_id']);
            $arrCurrentTeam = [];
            $arrCurrentEmp = [];
            $nameTest = Test::find($data['test_id']);
            foreach ($currentTest as $item) {
                if ($item->team_id != null) {
                    $arrCurrentTeam[] = $item->team_id;
                }
                if ($item->employee_id != null) {
                    $arrCurrentEmp[] = $item->employee_id;
                }
            }
        }
        if (empty($data['time_from']) || empty($data['time_to'])) {
            $response['error'] = 1;
            $response['message'] = trans('test::test.No exam time entered');
            return $response;
        }
        if ($data['time_from'] > $data['time_to']) {
            $response['error'] = 1;
            $response['message'] = trans('test::test.Time to validate');
            return $response;
        }
        if (empty($data['team_id']) && empty($data['employee_id'])) {
            $response['error'] = 1;
            $response['message'] = trans('test::test.No parts or employees selected');
            return $response;
        } else {
            $nameTeam = "";
            $nameEmp = "";
            $nameTeamExist = "";
            $nameEmpExist = "";
            if (!empty($data['team_id'])) {
                foreach ($data['team_id'] as $item) {
                    if (in_array($item, $arrCurrentTeam)) {
                        $name = Team::find($item);
                        $nameTeam = $nameTeam . ", " . $name->name;
                    }
                }
                foreach ($arrCurrentTeam as $value) {
                    if (!in_array($value, $data['team_id'])) {
                        $name = Team::find($value);
                        $nameTeamExist = $nameTeamExist . ", " . $name->name;
                    }
                }
            } else {
                foreach ($arrCurrentTeam as $value) {
                    $name = Team::find($value);
                    $nameTeamExist = $nameTeamExist . ", " . $name->name;
                }
            }
            if (!empty($data['employee_id'])) {
                foreach ($data['employee_id'] as $item) {
                    if (in_array($item, $arrCurrentEmp)) {
                        $name = Employee::find($item);
                        $nameEmp = $nameEmp . ", " . $name->name;
                    }
                }
                foreach ($arrCurrentEmp as $value) {
                    if (!in_array($value, $data['employee_id'])) {
                        $name = Employee::find($value);
                        $nameEmpExist = $nameEmpExist . ", " . $name->name;
                    }
                }
            } else {
                foreach ($arrCurrentEmp as $value) {
                    $name = Employee::find($value);
                    $nameEmpExist = $nameEmpExist . ", " . $name->name;
                }
            }
            if ($nameEmpExist != "" || $nameTeamExist != "") {
                $deleted = trans('test::test.:item deleted from exam list.', ['item' => substr($nameTeamExist . $nameEmpExist, 2)]) . "\n\n" . trans('test::test.Do you want to reset!');
            } else {
                $deleted = "\n" . trans('test::test.Do you want to reset!');
            }
            if ($nameTeam != "" && $nameEmp != "") {
                $response['message'] = trans('test::test.Division :team has the :test test.', ['team' => substr($nameTeam, 2), 'test' => $nameTest->name]) . "\n" . trans('test::test.Employee :employee has the :test test.', ['employee' => substr($nameEmp, 2), 'test' => $nameTest->name]) . "\n" . $deleted;
                $response['error'] = 2;
                $response['data'] = $data;
                return $response;
            }
            if ($nameTeam != "") {
               $response['message'] = trans('test::test.Division :team has the :test test.', ['team' => substr($nameTeam, 2), 'test' => $nameTest->name]) . "\n" . $deleted;
                $response['error'] = 2;
                $response['data'] = $data;
                return $response;
            }
            if ($nameEmp != "") {
                $response['message'] = trans('test::test.Employee :employee has the :test test.', ['employee' => substr($nameEmp, 2), 'test' => $nameTest->name]) . "\n" . $deleted;
                $response['error'] = 2;
                $response['data'] = $data;
                return $response;
            }
            if ($nameEmpExist != "" || $nameTeamExist != "") {
                $response['message'] = $deleted;
                $response['error'] = 2;
                $response['data'] = $data;
                return $response;
            }
        }

        $this->save($data);
        $updateMultiTimes = Test::where('id', $data['test_id'])->first();
        $updateMultiTimes->multi_times = $data['multi_times'];
        $updateMultiTimes->save();
        $response['success'] = 1;
        $response['message'] = Lang::get('core::message.Save success');
        return response()->json($response);
    }

    public function save(array $data = [])
    {
        DB::beginTransaction();
        try {
            //delete all assignee by test id before insert new
            Assignee::where('test_id', $data['test_id'])->delete();
            if (!empty($data['team_id'])) {
                foreach ($data['team_id'] as $item) {
                    $assigneeTeam = Assignee::where('test_id', $data['test_id'])
                                            ->where('team_id', $item)
                                            ->first();
                    if ($assigneeTeam) {
                        $assigneeTeam->time_from = Carbon::parse($data['time_from'])->format('Y-m-d H:i:s');
                        $assigneeTeam->time_to = Carbon::parse($data['time_to'])->format('Y-m-d H:i:s');
                        $assigneeTeam->save();
                    } else {
                        $assigneeTeam = new Assignee();
                        $assigneeTeam->setData([
                            'test_id' => $data['test_id'],
                            'team_id' => $item,
                            'employee_id' => null,
                            'time_from' => Carbon::parse($data['time_from'])->format('Y-m-d H:i:s'),
                            'time_to' => Carbon::parse($data['time_to'])->format('Y-m-d H:i:s')
                        ]);
                        $assigneeTeam->save();
                    }
                }
            }
            if (!empty($data['employee_id'])) {
                foreach ($data['employee_id'] as $item) {
                    $assigneeEmp = Assignee::where('test_id', $data['test_id'])
                                            ->where('employee_id', $item)
                                            ->first();
                    if ($assigneeEmp) {
                        $assigneeEmp->time_from = Carbon::parse($data['time_from'])->format('Y-m-d H:i:s');
                        $assigneeEmp->time_to = Carbon::parse($data['time_to'])->format('Y-m-d H:i:s');
                        $assigneeEmp->save();
                    } else {
                        $assigneeEmp = new Assignee();
                        $assigneeEmp->setData([
                            'test_id' => $data['test_id'],
                            'team_id' => null,
                            'employee_id' => $item,
                            'time_from' => Carbon::parse($data['time_from'])->format('Y-m-d H:i:s'),
                            'time_to' => Carbon::parse($data['time_to'])->format('Y-m-d H:i:s')
                        ]);
                        $assigneeEmp->save();
                    }
                }
            }
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
        return true;
    }
}
