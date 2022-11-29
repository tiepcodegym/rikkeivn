<?php
namespace Rikkei\Project\View;

use Rikkei\Project\Model\Task;
use Rikkei\Project\Model\Project;
use Rikkei\Core\Model\EmailQueue;
use Rikkei\Team\Model\Employee;
use Rikkei\Project\Model\ProjectMember;

class CheckTaskCF
{
    /**
     * update information tasks customer feedback not resolve in order to send mail to PM and PQA.
     *
     * @return void
     */
    public static function sendMailCF($freequencyReport = null)
    {
        $taskTbl = Task::getTableName();
        $empTbl = Employee::getTableName();
        $projsTbl = Project::getTableName();
        $projsMemberTbl = ProjectMember::getTableName();
        $dataEmail = [];

        $dataEmails = Task::join($projsTbl, "{$projsTbl}.id", '=', "{$taskTbl}.project_id")
                            ->join($empTbl, "{$empTbl}.id", '=', "{$projsTbl}.manager_id")
                            ->leftJoin($projsMemberTbl, function ($join) use ($projsTbl, $projsMemberTbl) {
                                $join->on("{$projsMemberTbl}.project_id", '=', "{$projsTbl}.id")
                                     ->where("{$projsMemberTbl}.type", '=', ProjectMember::TYPE_PQA);
                            })
                            ->leftJoin("{$empTbl} as empTbl2", "{$projsMemberTbl}.employee_id", '=', "empTbl2.id")
                            ->where("{$projsTbl}.status", Project::STATUS_APPROVED)
                            ->whereIn("{$taskTbl}.status", [Task::STATUS_NEW, Task::STATUS_PROCESS]);
        if ($freequencyReport) {
            $dataEmails->where("{$taskTbl}.freequency_report", '=', $freequencyReport);
        }
        $dataEmails = $dataEmails->select(
                "{$taskTbl}.id",
                "{$taskTbl}.title",
                "{$projsTbl}.name",
                "{$taskTbl}.status",
                "{$empTbl}.name as manager_name",
                "{$empTbl}.email as manager_email",
                "empTbl2.email as pqa_email",
                "empTbl2.name as pqa_name"
        )->get();

        // groupBy dataEmails by manager project and pqa.
        $emailGroup = [];
        foreach ($dataEmails as $dataList) {
            if ($dataList->manager_email || ($dataList->manager_email == $dataList->pqa_email)) {
                $emailGroup[$dataList->manager_email]['task'][] = $dataList;
                $emailGroup[$dataList->manager_email]['name'] = $dataList->manager_name;
            }
            if ($dataList->pqa_email && ($dataList->pqa_email != $dataList->manager_email)) {
                $emailGroup[$dataList->pqa_email]['task'][] = $dataList;
                $emailGroup[$dataList->pqa_email]['name'] = $dataList->pqa_name;
            }
        }
        foreach ($emailGroup as $empEmail => $dataTasks) {
            if (!$empEmail) {
                continue;
            }
            $emailQueue = new EmailQueue();
            $emailQueue->setSubject('[Rikkeisoft Intranet] Task Customer feedback chưa resolved!')
                        ->setTemplate('project::task.email.feedback_task_mail', [
                                'dataEmail' => $dataTasks['task'], 'dear_name' => $dataTasks['name'],
                            ])
                        ->setTo($empEmail);
            $dataEmail[] = $emailQueue->getValue();
        }
        if ($dataEmail) {
            EmailQueue::insert($dataEmail);
        }
    }
}
