<?php

namespace Rikkei\Project\Model;

use Rikkei\Core\Model\CoreModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Rikkei\Project\View\GeneralProject;
use Rikkei\Team\Model\Employee;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Lang;

class ProjRewardEmployee extends CoreModel
{
    use SoftDeletes;
    
    protected $table = 'proj_reward_employees';
    protected $fillable = [
        'task_id',
        'employee_id',
        'type',
        'effort_resource',
        'reward_default',
        'reward_submit',
        'reward_confirm',
        'reward_approve',
        'comment'
    ];

    /**
     * create reward employee of type pm, dev, brse
     * 
     * @param object $task
     */
    public static function createRewardEmployee($task)
    {
        $rewardEmployees = self::select(DB::raw('count(*) as count'))
            ->where('task_id', $task->id)
            ->first();
        if ($rewardEmployees && $rewardEmployees->count) {
            return $rewardEmployees;
        }
        $membersApproved = ProjectMember::getPMDEVAprroved($task->project_id);
        $dataMember = [];
        $now = Carbon::now()->format('Y-m-d H:i:s');
        // caculator data member reward
        foreach ($membersApproved as $item) {
            $key = $item->employee_id . '-' . $item->type;
            $dataEffortResource = GeneralProject::getManDayAndEffortEachMonth(
                Carbon::parse($item->start_at), 
                Carbon::parse($item->end_at),
                $item->effort
            );
            if (!isset($dataMember[$key])) {
                $dataMember[$key]['task_id'] = $task->id;
                $dataMember[$key]['employee_id'] = $item->employee_id;
                $dataMember[$key]['type'] = $item->type;
                $dataMember[$key]['effort_resource'] = $dataEffortResource;
                $dataMember[$key]['created_at'] = $now;
                $dataMember[$key]['updated_at'] = $now;
            } else {
                $dataEffortResourceOld = $dataMember[$key]['effort_resource'];
                foreach ($dataEffortResource as $keyMonth => $dataEffortResourceMonth) {
                    if (isset($dataEffortResourceOld[$keyMonth])) {
                        $dataEffortResourceOld[$keyMonth] += $dataEffortResourceMonth;
                    } else {
                        $dataEffortResourceOld[$keyMonth] = $dataEffortResourceMonth;
                    }
                }
                $dataMember[$key]['effort_resource'] = $dataEffortResourceOld;
            }
        }
        // en_code effort resource to insrt db
        $dataInsert = [];
        foreach ($dataMember as $itemDataMember) {
            $itemDataMember['effort_resource'] = 
                json_encode($itemDataMember['effort_resource']);
            $dataInsert[] = $itemDataMember;
        }
        return self::insert($dataInsert);
    }

    /**
     * create new employee reward
     * @param object $task
     * @param array $dataEmployees
     * @return array
     */
    public static function createNewEmpReward($task, $dataEmployees)
    {
        if (!$dataEmployees) {
            return;
        }
        $empIds = [];
        $result = [];
        DB::beginTransaction();
        try {
            foreach ($dataEmployees as $rwItem) {
                if (!isset($rwItem['employee_id']) || !$rwItem['employee_id']) {
                    continue;
                }
                $item = static::where('task_id', $task->id)
                        ->where('employee_id', $rwItem['employee_id'])
                        //->where('type', ProjectMember::TYPE_REWARD)
                        ->first();
                if (!$item) {
                    $item = static::create([
                        'task_id' => $task->id,
                        'employee_id' => $rwItem['employee_id'],
                        'type' => ProjectMember::TYPE_REWARD
                    ]);
                    $result[$item->id] = $rwItem['submit'];
                } else {
                    $empIds[] = $item->employee_id;
                }
            }
            if ($empIds) {
                $exists = Employee::whereIn('id', $empIds)->get();
                $arrAccounts = [];
                if (!$exists->isEmpty()) {
                    foreach ($exists as $emp) {
                        $arrAccounts[] = $emp->getNickName();
                    }
                }
                if ($arrAccounts) {
                    DB::rollback();
                    return [
                        'status' => 0,
                        'message' => trans('project::message.employee_exists', ['employee' => implode(', ', $arrAccounts)])
                    ];
                }
            }

            DB::commit();
            return [
                'status' => 1,
                'data' => $result
            ];
        } catch (\Exception $ex) {
            \Log::info($ex);
            DB::rollback();
            return [
                'status' => 0,
                'message' => trans('project::message.Error system')
            ];
        }
    }
    
    /**
     * get all employee of reward
     * 
     * @param model $task
     * @return collection
     */
    public static function getRewardEmployess($task)
    {
        $tableEmployee = Employee::getTableName();
        $tableRewardEmployee = ProjRewardEmployee::getTableName();
        
        $collection = self::select([$tableRewardEmployee.'.id',
            $tableRewardEmployee.'.employee_id',
            $tableRewardEmployee.'.type', $tableRewardEmployee.'.effort_resource',
            $tableRewardEmployee.'.reward_submit', $tableRewardEmployee.'.reward_confirm',
            $tableRewardEmployee.'.reward_approve', $tableEmployee.'.name',
            $tableEmployee.'.email',
            DB::raw('(isnull('.$tableRewardEmployee.'.comment) || '
                .$tableRewardEmployee.'.comment = "") as no_comment')
            ])
            ->join($tableEmployee, $tableEmployee.'.id', '=', 
                $tableRewardEmployee.'.employee_id')
            ->where('task_id', $task->id)
            ->orderBy(DB::raw('IF(type = '. ProjectMember::TYPE_REWARD .', 0, type)'), 'desc');
        if (Employee::isUseSoftDelete()) {
            $collection->whereNull($tableEmployee.'.deleted_at');
        }
        return $collection->get();
    }
    
    /**
     * get all employee and total approve reward
     */
    public static function getTotalRewardEmployees($projectId)
    {
        $tableEmployee = Employee::getTableName();
        $tableRewardEmployee = self::getTableName();
        $taskTable = Task::getTableName();
        $tableProject = Project::getTableName();
        
        $collection = self::join($taskTable, $tableRewardEmployee.'.task_id', '=', $taskTable.'.id')
                ->join($tableEmployee, $tableEmployee.'.id', '=', $tableRewardEmployee.'.employee_id')
                ->join($tableProject, $tableProject.'.id', '=', $taskTable.'.project_id')
                ->where($tableProject.'.id', '=', $projectId)
                ->where($taskTable.'.status', '=', Task::STATUS_APPROVED)
                ->select($tableRewardEmployee.'.id',$tableRewardEmployee.'.employee_id',
                    $tableRewardEmployee.'.type', $tableRewardEmployee.'.effort_resource',
                    $tableRewardEmployee.'.reward_submit', $tableRewardEmployee.'.reward_confirm',
                    $tableRewardEmployee.'.reward_approve', $tableEmployee.'.name',
                    $tableEmployee.'.email', DB::raw('SUM('.$tableRewardEmployee.'.reward_approve) AS totalReward'))
                ->orderBy($tableRewardEmployee.'.type', 'desc')
                ->groupBy($tableRewardEmployee.'.employee_id');
                
        if (Employee::isUseSoftDelete()) {
            $collection->whereNull($tableEmployee.'.deleted_at');
        }
        return $collection->get();
    }
    
    /**
     * get all id in Team Dev
     * 
     * @param model $task
     * @return array
     */
    public static function getIdIsTypeDev($task) {
        $result = [];
        $ids = self::select('id')
                ->where('task_id', $task->id)
                ->whereIn('type', ProjectMember::getKeyTypeDevTeam())
                ->get()
                ->toArray();
        foreach ($ids as $key => $value) {
            $result[$key] = $value['id'];
        }
        return $result;
    }
    
    /**
     * update comment 
     * 
     * @return boolean
     */
    public static function updateComment() {
        $data = Input::all();
        if (!isset($data['id'])) {
            return;
        }
        $item = self::find($data['id']);
        $item->comment = $data['value'];
        if ($item->save()) {
            return true;
        }
        return false;
    }
    
    /**
     * get comment
     * @return json
     */
    public static function getComment() {
        $data = Input::all();
        if (!isset($data['id'])) {
            return;
        }
        $item = self::find($data['id']);
        $comment = ''.$item->comment;
        return response()->json($comment);
    }
}