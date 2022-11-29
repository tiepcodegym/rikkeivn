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

class CustomerCommunication extends ProjectWOBase
{
    use SoftDeletes;

    const IS_NOT_SYNC = 1;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'proj_customer_communication';

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
    protected $fillable = ['proj_id', 'customer', 'role', 'contact_address', 'responsibility'];

    /*
     * get all communication by project id
     * @param int
     * @return collection
     */
    public static function getCustomerCommunication($projectId)
    {
        $item = self::select(['id', 'customer', 'role', 'contact_address', 'responsibility'])
            ->where('proj_id', $projectId)
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
                $memCom->customer = $data['employee_1'];
                $memCom->role = implode(',', $data['role_1']);
                $memCom->contact_address = $data['contact_address_1'];
                $memCom->responsibility = $data['responsibility_1'];
                $memCom->updated_at = Carbon::now()->format('Y-m-d H:i:s');
            } else {
                $memCom = new CustomerCommunication();
                $memCom->proj_id = $data['project_id'];
                $memCom->customer = $data['employee_1'];
                $memCom->role = implode(',', $data['role_1']);
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
    public static function getContentTable($project)
    {
        $permission = View::checkPermissionEditWorkorder($project);
        $permissionEdit = $permission['persissionEditPM'] || $permission['permissionEditSubPM'] || $permission['permissionEditPqa'];
        $checkEditWorkOrder = Task::checkEditWorkOrder($project->id);
        $allMemberCommunication = self::getCustomerCommunication($project->id);
        return view('project::components.customer-role-com', ['permissionEdit' => $permissionEdit, 'checkEditWorkOrder' => $checkEditWorkOrder, 'getCustomerCommunication' => $allMemberCommunication, 'project' => $project, 'detail' => true])->render();
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
}