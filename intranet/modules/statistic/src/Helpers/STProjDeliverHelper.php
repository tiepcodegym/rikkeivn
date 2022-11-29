<?php

namespace Rikkei\Statistic\Helpers;

use Rikkei\Core\View\BaseHelper;
use Rikkei\Project\Model\ProjDeliverable;
use Rikkei\Project\Model\Project;
use Illuminate\Support\Facades\DB;

class STProjDeliverHelper
{
    use BaseHelper;

    /**
     * process count bug project
     */
    public static function getSTProjDeliver($dateFrom, $dateTo)
    {
        $committedDateColumn = "IF(t_pd.change_request_by = " . ProjDeliverable::CHANGE_BY_CUSTOMER . ", t_pd.re_commited_date, t_pd.committed_date)";
        return (DB::select("select t_pd.project_id as proj_id, {$committedDateColumn} as cmd, "
            . 't_pd.actual_date as atd, group_concat(t_tp.team_id SEPARATOR "-") as team_id '
            . 'from proj_deliverables as t_pd '
            . 'left join team_projs as t_tp on t_tp.project_id = t_pd.project_id '
                . 'and t_tp.deleted_at is null '
            . 'where t_pd.deleted_at is null '
            . 'and t_pd.status = ' . Project::STATUS_APPROVED
            . " and date({$committedDateColumn}) >= '{$dateFrom->format('Y-m-d')}' "
            . "and date({$committedDateColumn}) <= '{$dateTo->format('Y-m-d')}' "
            . 'group by t_pd.id '
            . 'order by cmd desc'
        ));
    }
}
