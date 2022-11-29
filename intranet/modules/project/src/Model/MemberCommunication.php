<?php

namespace Rikkei\Project\Model;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Rikkei\Api\Helper\Employee;
use Rikkei\Team\View\Permission;
use Illuminate\Database\Eloquent\SoftDeletes;
use Rikkei\Project\Model\Task;
use Rikkei\Core\View\CacheHelper;
use Lang;
use Rikkei\Project\View\View;

class MemberCommunication extends ProjectWOBase
{
    use SoftDeletes;

    const IS_NOT_SYNC = 1;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'proj_member_communication';

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['proj_id', 'employee_id', 'role', 'contact_address', 'responsibility'];

    /*
     * get all communication by project id
     * @param int
     * @return collection
     */
    public static function getMemberCommunicationByType($projectId, $type)
    {
        $item = self::select(['id', 'employee_id', 'role', 'contact_address', 'responsibility'])
            ->where('proj_id', $projectId)
            ->where('type', $type)
            ->whereNull('deleted_at')
            ->orderBy('id', 'asc')
            ->get();
        CacheHelper::put(self::KEY_CACHE_WO, $item, $projectId);
        return $item;
    }

    /*
     * add communication
     * @param array
     */
    public static function insertMemberCommunication($data)
    {
        try {
            if (!$data) {
                return false;
            }
            if (isset($data['id'])) {
                $memCom = self::find($data['id']);
                $memCom->employee_id = $data['employee_1'];
                $memCom->role = implode(',', $data['role_1']);
                $memCom->type = $data['type'];
                $memCom->contact_address = $data['contact_address_1'];
                $memCom->responsibility = $data['responsibility_1'];
                $memCom->updated_at = Carbon::now()->format('Y-m-d H:i:s');
            } else {
                $memCom = new MemberCommunication();
                $memCom->proj_id = $data['project_id'];
                $memCom->employee_id = $data['employee_1'];
                $memCom->role = implode(',', $data['role_1']);
                $memCom->type = $data['type'];
                $memCom->is_not_sync = self::IS_NOT_SYNC;
                $memCom->contact_address = $data['contact_address_1'];
                $memCom->responsibility = $data['responsibility_1'];
                $memCom->created_at = Carbon::now()->format('Y-m-d H:i:s');
                $memCom->updated_at = Carbon::now()->format('Y-m-d H:i:s');
            }
            $memCom->save();
            DB::commit();
            CacheHelper::forget(self::KEY_CACHE_WO, $data['project_id']);
            return true;
        } catch (\Exception $ex) {
            DB::rollback();
            return false;
        }
    }

    /*
     * delete communication
     * @param array
     * @return boolean
     */
    public static function deleteMemberCommunication($data)
    {
        try {
            $memCom = self::find($data['id']);
            if (!isset($memCom)) {
                return false;
            }
            $memCom->deleted_at = Carbon::now()->format('Y-m-d H:i:s');
            $memCom->save();
            DB::commit();
            CacheHelper::forget(self::KEY_CACHE_WO, $data['project_id']);
            return true;
        } catch (\Exception $ex) {
            DB::rollback();
            return false;
        }
    }

    /**
     * get conten table after submit
     * @param array
     * @return string
     */
    public static function getContentTable($project, $type)
    {
        $permission = View::checkPermissionEditWorkorder($project);
        $permissionEdit = $permission['persissionEditPM'] || $permission['permissionEditSubPM'] || $permission['permissionEditPqa'];
        $checkEditWorkOrder = Task::checkEditWorkOrder($project->id);
        $allMemberCommunication = self::getMemberCommunicationByType($project->id, $type);
        if ($type == Task::TYPE_WO_MEMBER_COMMUNICATION) {
            return view('project::components.person-role-com', ['permissionEdit' => $permissionEdit, 'checkEditWorkOrder' => $checkEditWorkOrder, 'getMemberCommunication' => $allMemberCommunication, 'project' => $project, 'detail' => true])->render();
        }
        return view('project::components.customer-role-com', ['permissionEdit' => $permissionEdit, 'checkEditWorkOrder' => $checkEditWorkOrder, 'getCustomerCommunication' => $allMemberCommunication, 'project' => $project, 'detail' => true])->render();
    }

    public static function checkMember($projId, $memberId)
    {
        $isCheck = self::select('id', 'role')
            ->where('proj_id', $projId)
            ->where('employee_id', $memberId)
            ->whereNull('deleted_at')
            ->first();
        if ($isCheck) {
            return $isCheck;
        }
        return false;
    }

    /**
     * get value attribute
     * @param int $id, int $attribute
     * @return string $attribute
     */
    public static function getValueAttribute($id, $attribute) {
        $value = self::find($id);
        if ($value) {
            return nl2br(e($value->$attribute));
        }
        return null;
    }

    public static function getRoleCom($communication, $allRole)
    {
        $role = explode(",", $communication->role);
        $result = '';
        $countElement = 0;
        foreach($allRole as $roleMember => $val)
        {
            if (in_array($roleMember, $role)) {
                $countElement++;
                if ($countElement == 1) {
                    $result .= $val;
                } else {
                    $result .= ', ' . $val;
                }
            }
        }
        return $result;
    }

    public static function checkDeleted($projectId)
    {
        $memberNotDeleted = [];
        $memberCom = self::where('proj_id', $projectId)->whereNull('deleted_at')
            ->select('employee_id')
            ->get();
        $memberApprove = ProjectMember::where('project_id', $projectId)->where('status', ProjectMember::STATUS_APPROVED)
            ->select(DB::raw("group_concat(distinct employee_id SEPARATOR ', ') as proj_emp"))
            ->first();
        foreach ($memberCom as $member) {
            if (in_array($member->employee_id, explode(', ', $memberApprove['proj_emp']))) {
                continue;
            } else {
                $memberNotDeleted[] = $member->employee_id;
            }
        }
        return $memberNotDeleted;
    }
}