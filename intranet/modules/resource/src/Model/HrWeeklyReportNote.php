<?php

namespace Rikkei\Resource\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Team\View\Permission;
use Rikkei\Team\Model\TeamMember;
use Rikkei\Team\Model\Employee;
use Rikkei\Core\View\Form as CoreForm;

class HrWeeklyReportNote extends CoreModel
{
    protected $table = 'hr_weekly_report_note';
    protected $scopeRoute = 'resource::hr_wr.index';
    protected $fillable = ['week', 'email', 'note'];

    /**
     * get not by list candidate of week
     * @param type $list
     * @return type
     */
    public static function getNoteByWeeks($list)
    {
        if ($list->isEmpty()) {
            return [];
        }
        $weeks = $list->lists('week')->toArray();
        $collection = self::select('wrn.week', 'wrn.note', 'wrn.email')
                ->from(self::getTableName().' as wrn')
                ->whereIn('week', $weeks);
        if (Permission::getInstance()->isScopeCompany()) {
            //view all
        } elseif (Permission::getInstance()->isScopeTeam()) {
            $teamIds = TeamMember::where('employee_id', Permission::getInstance()->getEmployee()->id)
                    ->lists('team_id')
                    ->toArray();
            $collection->join(Employee::getTableName().' as emp', 'wrn.email', '=', 'emp.email')
                    ->join(TeamMember::getTableName().' as tmb', 'emp.id', '=', 'tmb.employee_id')
                    ->whereIn('tmb.team_id', $teamIds);
        } elseif (Permission::getInstance()->isScopeSelf()) {
            $collection->where('email', Permission::getInstance()->getEmployee()->email);
        } else {
            $collection->where('wrn.id', -1);
        }
        if ($filterRecruiter = CoreForm::getFilterData('excerpt', 'recruiter')) {
            $collection->where('wrn.email', $filterRecruiter);
        }
        $collection->groupBy('wrn.id')
                ->orderBy('wrn.updated_at', 'desc');
        return $collection->get()->groupBy('week');
    }

    /**
     * create or update item
     * @param string $week
     * @param string $note
     * @param string $email
     * @return array
     */
    public static function insertOrUpdate($week, $note = null, $email = null)
    {
        if (!$email) {
            $email = Permission::getInstance()->getEmployee()->email;
        }
        $data = [
            'week' => $week,
            'note' => $note,
            'email' => $email
        ];
        $item = self::where('week', $week)
                ->where('email', $email)
                ->first();
        if (!$item) {
            $item = self::create($data);
        } else {
            if ($item->email != Permission::getInstance()->getEmployee()->email) {
                return [
                    'error' => 1,
                    'message' => trans('resource::message.Error permission')
                ];
            }
            if (!trim($note)) {
                $item->delete();
                return [
                    'delete' => 1,
                    'note' => ''
                ];
            }
            $item->update($data);
        }
        return [
            'delete' => 0,
            'name' => ucfirst(preg_replace('/@.*/', '', $item->email)),
            'note' => $item->note
        ];
    }
}
