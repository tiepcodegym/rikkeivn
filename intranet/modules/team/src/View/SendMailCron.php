<?php
namespace Rikkei\Team\View;

use Rikkei\Team\Model\Employee;
use Carbon\Carbon;
use Rikkei\Core\Model\EmailQueue;
use Rikkei\Team\Model\EmployeeWork;
use Rikkei\Project\Model\Project;
use Rikkei\Project\Model\ProjectMember;
use Rikkei\Team\Model\Team;
use Rikkei\Resource\View\getOptions;
use Rikkei\Project\Model\ProjectWOBase;

class SendMailCron
{
    public static function cronMailNewStaff()
    {
        /*
        *get new staff of the previous month and group by team_Id
        */
        $dataEmail = [];
        $datePrevMonth = Carbon::now()->subMonth();
        $newStaffs = Employee::join('team_members', 'employees.id', '=', 'team_members.employee_id')
                    ->leftjoin('users', 'users.employee_id', '=', 'employees.id')
                    ->leftjoin('employee_contact', 'employee_contact.employee_id', '=', 'employees.id')
                    ->whereBetween('join_date', [$datePrevMonth->startOfMonth()->format('Y-m-d H:i:s'), $datePrevMonth->endOfMonth()->format('Y-m-d H:i:s')])
                    ->select('employees.id', 'employees.name', 'employees.birthday', 'employee_contact.native_addr', 'employees.email', 'employee_contact.mobile_phone', 'employee_contact.skype', 'team_id', 'users.avatar_url')
                    ->get()
                    ->groupBy('team_id')
                    ->toArray();
        /*
        *get old staff and group by team_id
        */
        $newTeamIds = array_keys($newStaffs);
        $oldStaffs = Employee::join('team_members', 'employees.id', '=', 'team_members.employee_id')
                        ->join('teams', 'team_members.team_id', '=', 'teams.id')
                        ->whereDate('join_date', '<', Carbon::now()->subMonths(3)->endOfMonth()->format('Y-m-d H:m:i'))
                        ->select('employees.id', 'employees.name', 'employees.email', 'team_id', 'teams.name AS team')
                        ->whereIn('team_id', $newTeamIds)
                        ->whereNull('leave_date')
                        ->get()
                        ->groupBy('team_id')
                        ->toArray();
        foreach ($oldStaffs as $teamId => $listOldStaffs) {
            if (!isset($newStaffs[$teamId])) {
                continue;
            }
            foreach ($listOldStaffs as $oldStaff) {
                $emailQueue = new EmailQueue();
                    $emailQueue->setSubject(trans('team::view.subject mail welcome new staff', ['month' => $datePrevMonth->format('m'), 'team' => $oldStaff['team']]))
                    ->setTemplate('team::mail.introduct_newStaff', [
                        'newStaffs' => $newStaffs[$teamId],
                        'teamName' => $oldStaff['team'],
                    ])
                    ->setTo($oldStaff['email']);
                    $dataEmail[] = $emailQueue->getValue();
            }
        }

        if ($dataEmail) {
            EmailQueue::insert($dataEmail);
        }
    }

    /**
     * send mail to employee outsourced about rental time deadline.
     */
    public static function sendMailEmployeeOutsourced()
    {
        $empTable = Employee::getTableName();
        $empWorkTable = EmployeeWork::getTableName();
        $projsMemberTable = ProjectMember::getTableName();
        $projsTable = Project::getTableName();
        $conditionDay = Carbon::today()->addWeeks(2)->toDateTimeString(); // employee will leave_date after 2 week.
        $teamTable = Team::getTableName();
        $dataEmail = [];

        $employees = EmployeeWork::select("{$empTable}.name",
                                            "{$empTable}.email",
                                            "{$empTable}.leave_date",
                                            "{$projsTable}.id as id_project",
                                            "{$projsTable}.name as name_project",
                                            "empTable2.name as name_manager",
                                            "empTable2.email as email_manager"
                                            )
                                    ->join($empTable, "{$empWorkTable}.employee_id", '=', "{$empTable}.id")
                                    ->join($projsMemberTable, function ($join) use ($projsMemberTable, $empTable) {
                                        $join->on("{$projsMemberTable}.employee_id", '=', "{$empTable}.id")
                                            ->where("{$projsMemberTable}.status", '=', ProjectWOBase::STATUS_APPROVED)
                                            ->whereNull("{$projsMemberTable}.deleted_at");
                                    })
                                    ->join($projsTable, "{$projsTable}.id", '=', "{$projsMemberTable}.project_id")
                                    ->join('employees as empTable2', 'empTable2.id', '=', "{$projsTable}.manager_id")
                                    ->where("{$projsTable}.status", '=', Project::STATUS_APPROVED)
                                    ->where("{$empWorkTable}.contract_type", '=', getOptions::WORKING_BORROW)
                                    ->whereIn("{$projsTable}.state", [Project::STATE_NEW, Project::STATE_PROCESSING])
                                    ->whereDate("{$empTable}.leave_date", '=', $conditionDay)
                                    ->get()
                                    ->unique(function ($item) {
                                        return $item['id_project'].$item['email'];
                                    });

        $leaderHr = Team::select("{$empTable}.name as name_leader",
                                "{$empTable}.email as email_leader"
                                )
                        ->join($empTable, "{$empTable}.id", '=', "{$teamTable}.leader_id")
                        ->where("{$teamTable}.code", '=', 'hanoi_hr')
                        ->get();

//        group data email follow email.
        $emailGroup = [];
        $emailCheck = [];
        foreach ($employees as $employee) {
            if ($employee->email_manager) {
                $emailGroup[$employee->email_manager]['emp'][] = $employee;
                $emailGroup[$employee->email_manager]['dear_name'] = $employee->name_manager;
                $emailCheck[] = $employee->email_manager;
            }
            if (in_array($employee->email, $emailCheck)) {
                continue;
            }
            if ($employee->email) {
                $emailGroup[$employee->email]['emp'][] = $employee;
                $emailGroup[$employee->email]['dear_name'] = $employee->name;
            }

            foreach ($leaderHr as $leader) {
                if ($leader->email_leader && !in_array($leader->email_leader, $emailCheck)) {
                    $emailGroup[$leader->email_leader]['emp'][] = $employee;
                    $emailGroup[$leader->email_leader]['dear_name'] = $leader->name_leader;
                }
            }
        }

        foreach ($emailGroup as $empEmail => $dataEmp) {
            $title = false;
            if (!$empEmail) {
                continue;
            }
            foreach ($dataEmp['emp'] as $item) {
                if ($empEmail == $item->email) {
                    $title = true;
                }
            }

            $emailQueue = new EmailQueue();
            $emailQueue->setSubject('[Rikkeisoft Intranet] Outsourcing employee is about to expire borrow!')
                        ->setTemplate('team::mail.notification_emp_borrow', [
                            'dataEmail' => $dataEmp['emp'], 'dear_name' => $dataEmp['dear_name'], 'titleMail' => $title,
                        ])
                        ->setTo($empEmail);
            $dataEmail[] = $emailQueue->getValue();
        }
        if ($dataEmail) {
            EmailQueue::insert($dataEmail);
        }
    }

}
