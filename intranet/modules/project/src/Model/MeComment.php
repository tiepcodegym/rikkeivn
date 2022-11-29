<?php

namespace Rikkei\Project\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Project\Model\ProjectMember;
use Rikkei\Team\Model\Employee;
use Rikkei\Core\Model\User;
use Rikkei\Team\View\Permission;
use Rikkei\Project\Model\MeActivity;
use Carbon\Carbon;

class MeComment extends CoreModel {

    protected $table = 'me_comments';
    protected $fillable = ['eval_id', 'attr_id', 'employee_id', 'employee_name', 'type', 'content', 'comment_type', 'is_activity'];
    protected $appends = ['type_class'];

    const GL_TYPE = 1;
    const PM_TYPE = 2;
    const ST_TYPE = 3;
    const COO_TYPE = 10;
    
    const TYPE_COMMENT = 1;
    const TYPE_NOTE = 2;
    const TYPE_LATE_TIME = 3;
    
    const PER_PAGE = 10;

    /**
     * get evaluation
     * @return object
     */
    public function evaluation(){
        return $this->belongsTo('\Rikkei\Project\Model\MeEvaluation', 'eval_id', 'id');
    }
    
    public function attr() {
        return $this->belongsTo('\Rikkei\Project\Model\MeComment', 'attr_id', 'id');
    }
    
    /**
     * get comment by attribute id
     * @param type $eval_id
     * @param type $attr_id
     * @return collection
     */
    public static function getByEvalAttr($eval_id, $attr_id = null) {
        $employeeTbl = Employee::getTableName();
        $userTbl = User::getTableName();
        $commentTbl = self::getTableName();
        $collection = self::join($employeeTbl.' as epl', $commentTbl.'.employee_id', '=', 'epl.id')
                ->join($userTbl.' as user', $commentTbl.'.employee_id', '=', 'user.employee_id')
                ->select($commentTbl.'.*', 'user.employee_id', 'user.google_id', 'user.avatar_url', 'epl.name')
                ->where($commentTbl.'.eval_id', $eval_id);
        if (!$attr_id) {
            $collection = $collection->where('comment_type', self::TYPE_NOTE);
        } else {
            $collection = $collection->where($commentTbl.'.attr_id', $attr_id);
        }
        return $collection->orderBy($commentTbl.'.created_at', 'asc')->paginate(self::PER_PAGE);
    }

    /**
     * check is current user commented in attrId
     * @param integer $evalId
     * @param integer $attrId
     * @return boolean
     */
    public static function isUserComment($evalId, $attrId)
    {
        $item = self::where('employee_id', auth()->id())
                ->where('eval_id', $evalId)
                ->where('attr_id', $attrId)
                ->first();
        if ($item) {
            return 1;
        }
        return 0;
    }

    /**
     * get current user type in project
     * @param type $project_id
     * @return int
     */
    public static function getCurrentUserInProjectType($project_id, $current_user = null) {
        if (!$current_user) {
            $current_user = Permission::getInstance()->getEmployee();
        }
        $member = ProjectMember::where('project_id', $project_id)
                ->where('employee_id', $current_user->id)
                ->orderBy('end_at', 'desc')
                ->first();
        
        $type = self::ST_TYPE;
        if ($member) {
            switch ($member->type) {
                case ProjectMember::TYPE_PM:
                    $type = self::PM_TYPE;
                    break;
                default:
                    $type = self::ST_TYPE;
                    break;
            }
        }
        if ($current_user->isLeader()) {
            if ($type != self::PM_TYPE) {
                $type = self::GL_TYPE;
            }
        }
        if (Permission::getInstance()->isAllow('project::me.coo_edit_point')) {
            if ($type != self::PM_TYPE) {
                $type = self::COO_TYPE;
            }
        }
        return $type;
    }

    /**
     * render css class type
     * @return string
     */
    public static function classType($type = null) {
        switch ($type) {
            case self::GL_TYPE:
                return '_gl_type';
            case self::PM_TYPE:
                return '_pm_type';
            case self::ST_TYPE:
                return '_st_type';
            case self::COO_TYPE:
                return '_coo_type';
            default:
                return '';
        }
    }
    
    public function getTypeClassAttribute() {
        return self::classType($this->type);
    }
    
    public static function checkUserCommentEval($eval_id, $user_id = null) {
        if (!$user_id) {
            $user_id = Permission::getInstance()->getEmployee()->id;
        }
        return self::where('eval_id', $eval_id)
                ->where('employee_id', $user_id)
                ->first();
    }

    /*
     * insert comment activity
     */
    public static function insertActivities($evalItem)
    {
        $activities = MeActivity::getByEmpId($evalItem->eval_time->format('Y-m'), $evalItem->employee_id);
        if (!$activities) {
            return;
        }
        $dataInsert = [];
        $timeNow = \Carbon\Carbon::now()->toDateTimeString();
        $comments = self::where('eval_id', $evalItem->id)
                ->where('is_activity', 1)
                ->get()
                ->groupBy('attr_id');
        //if not have comment then insert
        foreach ($activities as $attrId => $content) {
            $commentContent = trim($content->first()->content);
            if (isset($comments[$attrId])) {
                if (!$commentContent) {
                    $comments[$attrId]->first()->delete();
                } else {
                    $comments[$attrId]->first()->update([
                        'content' => $content->first()->content,
                        'updated_at' => $timeNow
                    ]);
                }
            } elseif ($commentContent) {
                $dataInsert[] = [
                    'eval_id' => $evalItem->id,
                    'attr_id' => $attrId,
                    'employee_id' => $evalItem->employee_id,
                    'type' => self::ST_TYPE,
                    'content' => $content->first()->content,
                    'comment_type' => self::TYPE_COMMENT,
                    'is_activity' => 1,
                    'created_at' => $timeNow,
                    'updated_at' => $timeNow
                ];
            } else {
                //
            }
        }
        if ($dataInsert) {
            self::insert($dataInsert);
        }
    }

    /*
     * update comment activity
     */
    public static function updateActivityComment($empId, $data)
    {
        $month = Carbon::parse($data['month']);
        $comment = self::select('cm.*', 'me.id as eval_id')
                ->from(self::getTableName() . ' as cm')
                ->join(MeEvaluation::getTableName() . ' as me', 'cm.eval_id', '=', 'me.id')
                ->where('me.eval_time', $month->startOfMonth()->toDateTimeString())
                ->where('me.status', '!=', MeEvaluation::STT_CLOSED)
                ->where('cm.employee_id', $empId)
                ->where('cm.attr_id', $data['attr_id'])
                ->where('cm.is_activity', 1)
                ->first();
        if (!$comment) {
            return;
        }
        $content = trim($data['content']);
        if (!$content) {
            $comment->delete();
        } else {
            $comment->update(['content' => $content]);
        }
    }

    /*
     * before delete
     */
    public function delete() {
        if ($this->is_activity) {
            $activity = MeActivity::select('mac.*')
                ->from(MeActivity::getTableName() . ' as mac')
                ->join(MeEvaluation::getTableName() . ' as me', function ($join) {
                    $join->on('mac.employee_id', '=', 'me.employee_id')
                            ->where('me.id', '=', $this->eval_id);
                })
                ->where('mac.month', '=', \DB::raw('DATE_FORMAT(me.eval_time, "%Y-%m")'))
                ->where('mac.attr_id', $this->attr_id)
                ->groupBy('mac.id')
                ->first();
            if ($activity) {
                $activity->delete();
            }
        }
        return parent::delete();
    }

}
