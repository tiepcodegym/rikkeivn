<?php

namespace Rikkei\Resource\View;

use Rikkei\Notify\Classes\RkNotify;
use Rikkei\Resource\Model\ResourceRequest;
use Rikkei\Resource\Model\Candidate;
use Rikkei\Core\Model\EmailQueue;
use Lang;
use Rikkei\Team\Model\Employee;
use Rikkei\Project\Model\ProjectMember;
use Rikkei\Core\Model\CoreConfigData;
use Rikkei\Team\Model\Team;
use Rikkei\Team\Model\TeamMember;
use Illuminate\Support\Facades\Config as SupportConfig;

class CronData
{
    /**
     * Send mail notify to close requests when requests have expried or recruited enought
     */
    public static function cronMailCloseRequest()
    {
        $listRequest = ResourceRequest::where('requests.status', getOptions::STATUS_INPROGRESS)
                //->where('requests.type', getOptions::TYPE_RECRUIT)
                ->select('created_by', 'id', 'title', 'deadline')
                ->get();

        foreach ($listRequest as $rq) {
            if ($rq->deadline < date('Y-m-d') || Candidate::checkFull($rq)) {
                $createdPerson = Employee::getEmpById($rq->created_by);
                if ($createdPerson) {
                    $emailQueue = new EmailQueue();
                    $emailQueue->setTo($createdPerson->email)
                    ->setFrom(Config('mail.username'), Config('mail.name'))
                    ->setSubject(Lang::get('resource::view.[Rikkeisoft] Please close request `:title`', ['title' => $rq->title]))
                    ->setTemplate('resource::request.mail_notify_close', [
                        'url' => route('resource::request.edit', $rq->id),
                        'title' => $rq->title,
                        'name' => $createdPerson->name,
                    ])
                    ->setNotify($rq->created_by, null, route('resource::request.edit', $rq->id), ['icon' => 'resource.png', 'category_id' => RkNotify::CATEGORY_HUMAN_RESOURCE]);
                    $emailQueue->save();
                }
            }
        }
    }

    public static function cronMailUtilization()
    {
        $model = new ProjectMember();

        //Send mail to bod
        $startDate = date('Y-m-d', strtotime('monday 1 week ago'));
        $endDate = date('Y-m-d', strtotime('sunday last week'));
        $employees = $model->getMembersNotInAnyProject($startDate, $endDate);
        if ($employees) {
            $dataTeam = View::groupEmployeesByTeam($employees);
            $mailSubject = Lang::get('resource::view.[Rikkeisoft] The list of Employee has effort 0% from :start - :end', ['start' => date('d/m/Y', strtotime($startDate)), 'end' => date('d/m/Y', strtotime($endDate))]);
            $emailQueue = new EmailQueue();
            $emailQueue->setTo(CoreConfigData::getValueDB('bod_email'))
            ->setFrom(Config('mail.username'), Config('mail.name'))
            ->setSubject($mailSubject)
            ->setTemplate('resource::dashboard.cron.mail_utilization', [
                'startDate' => date('d/m/Y', strtotime($startDate)),
                'endDate' => date('d/m/Y', strtotime($endDate)),
                'dataTeam' => $dataTeam,
            ]);
            $emailQueue->save();
            //put notify
            $bodMembers = Team::getMemberOfBod();
            if (!$bodMembers->isEmpty()) {
                \RkNotify::put(
                    $bodMembers->lists('id')->toArray(),
                    $mailSubject,
                    route('resource::dashboard.utilization'),
                    ['icon' => 'resource.png', 'category_id' => RkNotify::CATEGORY_HUMAN_RESOURCE]
                );
            }
        }

        //Send mail to leader and subleader of each Division
        $startDate = date('Y-m-d', strtotime('monday last week'));
        $employees = $model->getMembersNotInAnyProject($startDate, $endDate);
        if ($employees) {
            $dataTeam = View::groupEmployeesByTeam($employees);
            $mailSubject = Lang::get('resource::view.[Rikkeisoft] The list of Employee has effort 0% from :start - :end', ['start' => date('d/m/Y', strtotime($startDate)), 'end' => date('d/m/Y', strtotime($endDate))]);
            $recieverIds = [];
            foreach ($dataTeam  as $teamId => $listEmp) {
                $teamInfo = Team::getTeamById($teamId);
                $leaderAndSubleaderInfo = TeamMember::getListLeaderByTeamIds([$teamInfo->id]);
                foreach ($leaderAndSubleaderInfo as $leaderInfo) {
                    if ($leaderInfo) {
                        $emailQueue = new EmailQueue();
                        $emailQueue->setTo($leaderInfo->email)
                        ->setFrom(Config('mail.username'), Config('mail.name'))
                        ->setSubject($mailSubject)
                        ->setTemplate('resource::dashboard.cron.mail_utilization_leader', [
                            'startDate' => date('d/m/Y', strtotime($startDate)),
                            'endDate' => date('d/m/Y', strtotime($endDate)),
                            'teamName' => $teamInfo->name,
                            'listEmp' => $listEmp,
                            'recipientName' => $leaderInfo->name,
                        ]);
                        $emailQueue->save();
                        $recieverIds[] = $leaderInfo->id;
                    }
                }
            }
            //put notify
            if ($recieverIds) {
                \RkNotify::put($recieverIds, $mailSubject, route('resource::dashboard.utilization'), ['icon' => 'resource.png', 'category_id' => RkNotify::CATEGORY_HUMAN_RESOURCE]);
            }
        }
    }
}
