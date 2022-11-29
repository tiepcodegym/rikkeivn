<?php
namespace Rikkei\Project\View;

use Rikkei\Project\Model\Task;
use Carbon\Carbon;
use Rikkei\Core\Model\EmailQueue;
use Rikkei\Team\Model\Employee;

class CheckDeadlineMail
{
    /**
     * update information tasks deadline to send mail cron.
     *
     * @return void
     */
    public static function cronMail()
    {
        $dataEmail = [];
        $curDate = Carbon::today();
        // get staff send mail deadline.
        $dataEmails = Task::join('task_assigns', 'task_assigns.task_id', '=', 'tasks.id')
                        ->join('employees', 'task_assigns.employee_id', '=', 'employees.id')
                        ->leftjoin('employees as creator', 'creator.id', '=', 'tasks.created_by')
                        ->whereDate('tasks.duedate', '<=', $curDate->addDay()->format('Y-m-d H:i:s'))
                        ->whereNotNull('tasks.duedate')
                        ->whereIn('tasks.status', [1, 2, 4])
                        ->where('task_assigns.role', '=', 0)
                        ->select('employees.name', 'employees.email', 'tasks.id', 'employees.id as employees_id', 'tasks.title', 'tasks.duedate', 'tasks.created_by', 'creator.name as creator_name', 'creator.email as creator_email')
                        ->get();
        // groupBy dataEmails by assginee
        $dataByAssigns = $dataEmails->groupBy('email')->toArray();
        // groupBy dataEmails by creator
        $dataByCreators = $dataEmails->groupBy('creator_email')->toArray();
        $emailGroup['dataByAssigns'] = $dataByAssigns;
        $emailGroup['dataByCreators'] = $dataByCreators;
        // send mail to staff assign task dealine.
        foreach ($emailGroup as $dataTasks) {
            foreach ($dataTasks as $empEmail => $listdataTasks) {
                $emailQueueAssigns = new EmailQueue();
                $emailQueueAssigns->setSubject(trans('project::view.Tasks coming to deadline'))
                                ->setTemplate('project::task.email.deadline_mail', [
                                    'dataEmail' => $listdataTasks,
                                ])
                                ->setTo($empEmail);
                $dataEmail[] = $emailQueueAssigns->getValue();
            }
        }
        if ($dataEmail) {
            EmailQueue::insert($dataEmail);
        }
    }
}
