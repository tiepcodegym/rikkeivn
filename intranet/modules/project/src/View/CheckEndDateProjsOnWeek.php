<?php
namespace Rikkei\Project\View;

use Rikkei\Core\Model\EmailQueue;
use Rikkei\Team\Model\Employee;
use Rikkei\Project\Model\Project;
use Carbon\Carbon;

class CheckEndDateProjsOnWeek
{
    /**
     * update information tasks deadline to send mail cron.
     *
     * @return void
     */
    public static function endDateMailProjsOnWeek()
    {
        $dataEmail = [];
        $dataProjects = Project::getProjectEndDateOfWeek();
        // group dataEmails by email manager project.
        $dataByManager = $dataProjects->groupBy('manager_email')->toArray();

        // group dataEmails by email PQA project.
        $dataByPQA = [];
        $listProjects = $dataProjects->toArray();
        foreach ($listProjects as $proj) {
            if (!$proj['pqa_email']) {
                continue;
            }
            $strPqaEmails = $proj['pqa_email'];
            $arrPqaEmails = explode(',', $strPqaEmails);
            foreach ($arrPqaEmails as $key => $pqaEmail) {
                if (!isset($dataByPQA[$pqaEmail])) {
                    $dataByPQA[$pqaEmail] = [];
                }
                $dataByPQA[$pqaEmail][] = $proj;
            }
        }
        $emailGroup['dataByManager'] = $dataByManager;
        $emailGroup['dataByPQA'] = $dataByPQA;
        $role = '';

        // send mail end date on week.
        $listEmails = [];
        foreach ($emailGroup as $key => $data) {
            foreach ($data as $email => $listProjs) {
                if (in_array($email, $listEmails)) {
                    continue;
                }
                $listEmails[] = $email; 
                $emailProjsEndDate = new EmailQueue();
                $emailProjsEndDate->setSubject(trans('project::view.Project coming to end date on week'))
                                ->setTemplate('project::emails.report_projs_end_date_on_week', [
                                    'dataEmail' => $listProjs, 'email' => $email,
                                ])
                                ->setTo($email);
                $dataEmail[] = $emailProjsEndDate->getValue();
            }
        }
        if ($dataEmail) {
            EmailQueue::insert($dataEmail);
        }
    }

    /**
     * check date on current week return true if date insite [dayStartOfCurrentWeek, dayEndOfCurrentWeek].
     *
     * @param object time.
     * @return boolean.
     */
    public static function checkDayOnWeek($date)
    {
        $dayStartOfWeek = Carbon::now()->startOfWeek()->toDateTimeString();
        $dayEndOfWeek = Carbon::now()->endOfWeek()->toDateTimeString();

        return (($date->gte($dayStartOfWeek)) && ($date->lte($dayEndOfWeek)));
    }
}
