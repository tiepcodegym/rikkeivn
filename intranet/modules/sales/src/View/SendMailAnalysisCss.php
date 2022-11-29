<?php

namespace Rikkei\Sales\View;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Rikkei\Notify\Classes\RkNotify;
use Rikkei\Project\Model\Project;
use Rikkei\Project\Model\ProjectMember;
use Rikkei\Core\Model\EmailQueue;
use Rikkei\Sales\Model\Css;
use Rikkei\Sales\Model\CssResult;
use Rikkei\Team\Model\Action;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\Model\EmployeeRole;
use Rikkei\Team\Model\Permission;
use Rikkei\Team\Model\PqaResponsibleTeam;
use Rikkei\Team\Model\TeamMember;
use Rikkei\Team\Model\Permission as PermissionModel;
use Rikkei\Team\Model\Team;

class SendMailAnalysisCss
{
    /**
     * Send mail to pm, sales, pqa, leader of project when submit status css.
     * if $option['status'] = true, send mail when customer css feedback .
     * 
     * @param $css
     * @param $cssResultId
     * @param $option default is empty.
     * @return void
     */
    public static function sendMailConfirmCss($css, $cssResultId, $option = [])
    {
        $project = Project::find($css->projs_id);
        $relaterIds = [];
        $leaderTeamPqa = Team::getLeaderOfTeam(Team::TEAM_PQA_ID);
        $getTeamInChargeOfProject = Project::getTeamInChargeOfProject($project->id);
        $pqaTeamCharge = PqaResponsibleTeam::getEmpIdResponsibleTeamAsTeamId($getTeamInChargeOfProject->team_id);

        if (isset($pqaTeamCharge)) {
            $relaterIds = array_merge($pqaTeamCharge, [(int)$leaderTeamPqa], (array)$project->manager_id);
        }
        $pqaInProject = Project::getEmpByRoleNotApproveInProject($project->id, ProjectMember::TYPE_PQA);
        if (!empty($pqaInProject)) {
            foreach ($pqaInProject as $pqa) {
                $relaterIds[] = $pqa->id;
            }
        }

        if ($css->status == CssResult::STATUS_SUBMITTED || $css->status == CssResult::STATUS_FEEDBACK) {
            $relaters = Employee::getEmpByIds($relaterIds);
        } else {
            $relaterIds = array_merge($relaterIds, (array)$project->leader_id);
            $relaters = Employee::getEmpByIds($relaterIds);
        }
        $dataEmail = [];
        $emailGroup = [];
        $emailCheck = [];

        foreach ($relaters as $relater) {
            if (in_array($relater->email, $emailCheck)) {
                continue;
            }
            $emailCheck[] = $relater->email;
            $relater->cssResultId = $cssResultId;
            $emailGroup[$relater->email][] = $relater;
        }

        // send mail
        $subject = '';
        if (isset($option['content']) && $option['status'] == Css::STATUS_FEEDBACK) {
            $subject = '[Rikkeisoft Intranet] Css analysis has been feedback!';
        } elseif (isset($option['status']) && $option['status'] == Css::STATUS_APPROVED) {
            $subject = '[Rikkeisoft Intranet] Css analysis has been approved!';
        } elseif (isset($option['status']) && $option['status'] == Css::STATUS_REVIEW) {
            $subject = '[Rikkeisoft Intranet] Css analysis has been reviewed!';
        } else {
            $subject = '[Rikkeisoft Intranet] Css analysis has been submitted!';
        }
        foreach ($emailGroup as $email => $relater) {
            foreach ($relater as $item) {
                $emailQueue = new EmailQueue();
                $emailQueue->setSubject($subject)
                            ->setTemplate('sales::css.email.submit_analysis_css', [
                                'cssResultId' => $item->cssResultId,
                                'dear_name' => $item->name,
                                'option' => $option,
                            ])
                            ->setTo($email);

                $dataEmail = $emailQueue->getValue();
            }
            if ($dataEmail) {
                try {
                    EmailQueue::insert($dataEmail);
                    //set notify
                    \RkNotify::put(
                        Employee::where('email', $email)->lists('id')->toArray(),
                        $subject,
                        route("sales::css.detail", ['id' => $cssResultId]),
                        ['category_id' => RkNotify::CATEGORY_PROJECT]
                    );
                } catch (Exception $ex) {

                }
            }
        }
    }
}
