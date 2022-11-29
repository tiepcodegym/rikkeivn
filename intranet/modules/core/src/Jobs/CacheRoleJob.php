<?php

namespace Rikkei\Core\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use DB;
use Illuminate\Support\Facades\Log;
use Rikkei\Core\Model\User;
use Rikkei\ManageTime\View\ManageTimeCommon;
use Rikkei\Team\Model\EmployeeRole;

class CacheRoleJob extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /**
     * ID employee
     * @var int $employeeId
     */
    private $employeeId;


    private $specialRoleId;

    private $teamId;

    public function __construct($employeeId = 0, $specialRoleId = 0, $teamId = 0)
    {
        $this->specialRoleId = (int)$specialRoleId;
        $this->employeeId = (int)$employeeId;
        $this->teamId = (int)$teamId;
    }

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 180;


    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            if ($this->specialRoleId) {
                //return $this->actionCacheRoleBySpecialId();
            }
            if ($this->teamId) {
                //return $this->actionCacheRoleByTeam();
            }
            //return $this->actionCacheRole();
        } catch (\Exception $exception) {
            Log::error($exception->getMessage());
            throw $exception;
        }
    }

    /**
     * @param bool $isDeleted
     */
    private function actionCacheRole()
    {
        Log::error("Start run cache role employee: " . date('Y-m-d H:i:s'));
        $startRun = date('Y-m-d H:i:s');
        $employeeId = $this->employeeId ? $this->employeeId : null;
        try {
            User::changeRoles($employeeId);
            $endRunTime = date('Y-m-d H:i:s');
            Log::error("\n[Trigger cache role success]  start [$startRun] end [$endRunTime]");
        } catch (\Exception $exception) {
            $endRunTime = date('Y-m-d H:i:s');
            Log::error("\n [Trigger cache role failed]  [Error] $startRun [End] $endRunTime  [errors] {$exception->getMessage()}");
        }
        return true;
    }


    /**
     * cache role nhân viên theo team
     */
    private function actionCacheRoleByTeam()
    {
        Log::info('start team role');
        $teams = ManageTimeCommon::getTeamChild($this->teamId);
        DB::table('team_members AS a')
            ->select('c.id')
            ->leftJoin('teams AS b', 'a.team_id', '=', 'b.id')
            ->join('employees AS c', 'a.employee_id', '=', 'c.id')
            ->whereIn('a.team_id', $teams)
            ->whereNull('c.leave_date')
            ->groupBy('c.id')
            ->whereNull('c.deleted_at')
            ->chunk(100, function ($employees) {
                foreach ($employees as $employee) {
                    User::changeRoles($employee->id);
                }
            });
        Log::info('end team role');
    }

    /**
     * cache role nhân viên theo vai trò đặc biệt
     */
    private function actionCacheRoleBySpecialId()
    {
        //Chỉ xét user trong nhóm special role
        Log::info('start special role');
        EmployeeRole::select('employee_id')->where('role_id', $this->specialRoleId)->groupBy('employee_id')->chunk(100, function ($employees) {
            foreach ($employees as $employee) {
                Log::info('nhân viên cache role:' . $employee->employee_id);
                User::changeRoles($employee->employee_id);
            }
        });
        Log::info('end special role');
    }
}
