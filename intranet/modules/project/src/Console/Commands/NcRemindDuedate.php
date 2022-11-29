<?php

namespace Rikkei\Project\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Rikkei\Core\Model\EmailQueue;
use Rikkei\Team\Model\Employee;
use Rikkei\Project\Model\TaskAssign;
use Rikkei\Project\Model\TaskNcmRequest;
use Rikkei\Project\Model\Task;

class NcRemindDuedate extends Command
{    
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'project:nc_remind_duedate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Nc remind duedate';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            \Log::info("======= Start Nc remind duedate =======\n");
            $this->info("======= Start Nc remind duedate =======\n");
            
            $ncDuedates = TaskNcmRequest::getNcDuedateInMonthAgo();
            $dataConvert = [];
            foreach ($ncDuedates as $item) {
                if (!empty($item->status)) {
                    if (in_array($item->status, [Task::STATUS_NEW, Task::STATUS_PROCESS])) {
                        $empGenerals = $this->getEmpMail($item->id);
                        foreach ($empGenerals as $value) {
                            if (isset($dataConvert['general'][$value->emp_email])) {
                                $dataConvert['general'][$value->emp_email]['nc'][$value->task_id] = $value->task_title;
                            } else {
                                $dataConvert['general'][$value->emp_email]['data'] = [
                                    'emp_id' => $value->employee_id,
                                    'emp_name' => $value->emp_name,
                                    'emp_email' => $value->emp_email
                                ];
                                $dataConvert['general'][$value->emp_email]['nc'][$value->task_id] = $value->task_title;
                            }
                        }
                    }
                    if ($item->status == Task::STATUS_RESOLVE2) {
                        $empMailCloseds = $this->getEmpMail($item->id, true);
                        foreach ($empMailCloseds as $valueClosed) {
                            if (isset($dataConvert['closed'][$valueClosed->emp_email])) {
                                $dataConvert['closed'][$valueClosed->emp_email]['nc'][$valueClosed->task_id] = $valueClosed->task_title;
                            } else {
                                $dataConvert['closed'][$valueClosed->emp_email]['data'] = [
                                    'emp_id' => $valueClosed->employee_id,
                                    'emp_name' => $valueClosed->emp_name,
                                    'emp_email' => $valueClosed->emp_email
                                ];
                                $dataConvert['closed'][$valueClosed->emp_email]['nc'][$valueClosed->task_id] = $valueClosed->task_title;
                            }
                        }
                    }
                }
            }

            if (!empty($dataConvert)) {
                foreach ($dataConvert as $key => $itemEmp) {
                    if (count($itemEmp)) {
                        foreach ($itemEmp as $emp) {
                            $subject = '【NC】Có NC đã quá hạn nhưng chưa được xử lý';
                            $template = 'project::emails.commands.nc_no_process';
                            if ($key == 'closed') {
                                $subject = '【NC】Có NC đã quá hạn nhưng chưa được closed';
                                $template = 'project::emails.commands.nc_closed';
                            }
                            $emailQueue = new EmailQueue();
                            $emailQueue->setTo($emp['data']['emp_email'])
                                ->setSubject($subject)
                                ->setTemplate($template, $emp)
                                ->save();
                        }
                    }
                }
            }

            $this->info("======= End Nc remind duedate =======\n");
            \Log::info("======= End Nc remind duedate =======\n");
        } catch (\Exception $ex) {
            Log::info($ex);
            $this->info("======= Error Nc remind duedate =======\n");
            \Log::info("======= Error Nc remind duedate =======\n");
        }
    }

    public function getEmpMail($ncId, $addApprover = false)
    {
        $tblTaskAssign = TaskAssign::getTableName();
        $tblEmp = Employee::getTableName();
        $tblTask = Task::getTableName();
        $arrRole = [
            TaskAssign::ROLE_ASSIGNEE,
            TaskAssign::ROLE_REPORTER,
        ];
        if ($addApprover) {
            $arrRole[] = TaskAssign::ROLE_APPROVER;
        }

        return TaskAssign::select(
            "{$tblTaskAssign}.*",
            "{$tblTask}.title as task_title",
            "{$tblEmp}.name as emp_name",
            "{$tblEmp}.email as emp_email"
        )
        ->join("{$tblTask}", "{$tblTaskAssign}.task_id", '=', "{$tblTask}.id")
        ->join("{$tblEmp}", "{$tblTaskAssign}.employee_id", '=', "{$tblEmp}.id")
        ->where('task_id', $ncId)
        ->whereIn('role', $arrRole)
        ->get();
    }
}
