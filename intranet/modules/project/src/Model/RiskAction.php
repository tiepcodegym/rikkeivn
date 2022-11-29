<?php

namespace Rikkei\Project\Model;

use Rikkei\Core\Model\CoreModel;
use Illuminate\Support\Facades\Lang;

class RiskAction extends CoreModel
{
    protected $table = 'risk_actions';

    public $timestamps = false;

    const TYPE_ISSUE = 1;
    const TYPE_RISK_MITIGATION = 2;
    const TYPE_RISK_CONTIGENCY = 3;
    

    const STATUS_OPEN = 1;
    const STATUS_INPROGRESS = 2;
    const STATUS_CLOSE = 3;

    public static function delByRisk($riskId)
    {
        self::where('risk_id', $riskId)->delete();
    }

    public static function getByType($type, $riskId)
    {
        return self::join('employees', 'employees.id', '=', 'risk_actions.assignee')
                ->where('type', $type)
                ->where('risk_id', $riskId)
                ->select([
                    'risk_actions.*',
                    'employees.name as employee_name',
                ])
                ->get();
    }

    public static function getStatus()
    {
        return [
            self::STATUS_OPEN => Lang::get('project::view.Open'),
            self::STATUS_INPROGRESS => Lang::get('project::view.Inprogress'),
            self::STATUS_CLOSE => Lang::get('project::view.Close'),
        ];
    }
}
