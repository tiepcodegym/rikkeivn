<?php

namespace Rikkei\ManageTime\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Team\Model\Employee;
use Rikkei\Core\Model\User;
use Rikkei\Team\View\Config;
use Rikkei\ManageTime\View\ManageTimeConst as MTConst;
use Rikkei\Core\View\View as CoreView;

class WktComment extends CoreModel
{
    protected $table = 'working_time_comments';
    protected $fillable = ['wkt_id', 'content', 'type', 'created_by'];

    /*
     * insert data
     */
    public static function insertData($wktId, $data = [])
    {
        $data['created_by'] = auth()->id();
        $data['wkt_id'] = $wktId;
        $data['type'] = $data['status'];
        if ($data['status'] == MTConst::STT_WK_TIME_REJECT) {
            $data['content'] = $data['reject_reason'];
        } else {
            $data['content'] = trans('manage_time::view.Approved register');
        }
        return self::create($data);
    }

    public function getNickName()
    {
        if (!$this->emp_email) {
            return null;
        }
        return CoreView::getNickName($this->emp_email);
    }

    public function getClassColor()
    {
        if ($this->type == MTConst::STT_WK_TIME_APPROVED) {
            return 'text-green';
        }
        return '';
    }

    public function getTextStatus()
    {
        if ($this->type == MTConst::STT_WK_TIME_REJECT) {
            return '<span class="text-red">'. trans('manage_time::view.Not approve') .'</span>: ';
        }
        return '';
    }

    /*
     * get list data
     */
    public static function getGridData($wktId)
    {
        $pager = Config::getPagerData();
        $collection = self::select('cm.id', 'cm.content', 'cm.type', 'cm.created_at', 'emp.email as emp_email', 'emp.name as emp_name', 'user.avatar_url')
                ->from(self::getTableName() . ' as cm')
                ->join(Employee::getTableName() . ' as emp', 'cm.created_by', '=', 'emp.id')
                ->leftJoin(User::getTableName() . ' as user', 'emp.id', '=', 'user.employee_id')
                ->where('cm.wkt_id', $wktId)
                ->orderBy('cm.created_at', 'desc')
                ->groupBy('cm.id');
        return $collection->paginate($pager['limit']);
    }
}
