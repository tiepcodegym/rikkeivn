<?php
namespace Rikkei\Project\View;

use Rikkei\Project\Model\Risk;
use Rikkei\Project\Model\Task;
use Carbon\Carbon;
use Rikkei\Core\Model\EmailQueue;

class CheckWarningTask
{
    /**
     * check status tasks and update data email.
     *
     *@return void.
     */
    public static function checkWarningTaskRisk()
    {
        $dataEmail = [];
        $curDate = Carbon::today();
        
        // get data tasks warning from table risk.
        $riskWarnings = Risk::leftJoin('employees', 'proj_op_ricks.owner', '=', 'employees.id')
                        ->join('employees as creator', 'proj_op_ricks.created_by', '=', 'creator.id')
                        ->whereNull('proj_op_ricks.deleted_at')
                        ->where(function ($query) {
                            $query->whereNull('proj_op_ricks.solution_using')
                                ->orWhere('proj_op_ricks.solution_using', '');
                        })
                        ->whereIn('proj_op_ricks.status', [Risk::STATUS_OPEN, Risk::STATUS_HAPPEN])
                        ->where(function ($query) {
                            $query->where(function ($query1) {
                                $query1->where('proj_op_ricks.level_important', '=', Risk::LEVEL_HIGH)
                                    ->whereDate('proj_op_ricks.created_at', '<=', Carbon::today()->subDay()->format('Y-m-d H:i:s'));
                            });
                            $query->orWhere(function ($query2) {
                                $query2->whereIn('proj_op_ricks.level_important', [Risk::LEVEL_LOW, Risk::LEVEL_NORMAL])
                                    ->whereDate('proj_op_ricks.created_at', '<=', Carbon::today()->subDays(7)->format('Y-m-d H:i:s'));
                            });
                        })
                        ->select('proj_op_ricks.id', 'proj_op_ricks.content as content_task', 'proj_op_ricks.type', 'proj_op_ricks.created_at', 'employees.name', 'employees.email', 'creator.email as creator_email', 'creator.name as creator_name')
                        ->get();
        // group $riskWarnings task risks.
        $dataEmailCreators =  $riskWarnings->groupBy('creator_email')->toArray();
        $dataEmailAssigns =  $riskWarnings->groupBy('email')->toArray();
        $emailGroup['dataEmailAssigns'] = $dataEmailAssigns;
        $emailGroup['dataEmailCreators'] = $dataEmailCreators;

        // send mail to staff assign task warning.
        foreach ($emailGroup as $dataTasks) {
            foreach ($dataTasks as $empEmail => $listdataTasks) {
                if($empEmail == null) {
                    continue;
                }
                $emailQueueAssigns = new EmailQueue();
                $emailQueueAssigns->setSubject(trans('project::email.Tasks warning'))
                                ->setTemplate('project::emails.warning_task_risk', [
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
