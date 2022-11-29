<?php

namespace Rikkei\Project\Model;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Rikkei\Core\View\CacheHelper;
use Rikkei\Project\View\View;

class Security extends ProjectWOBase

{
    use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'proj_op_security';

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
    protected $fillable = ['proj_id', 'content', 'description', 'procedure', 'employee_id', 'period'];

    public static function getSecurity($id)
    {
         return self::select('id', 'content', 'description', 'procedure', 'period')
            ->where('proj_id', $id)
            ->whereNull('deleted_at')
            ->get();
    }

    public static function insertSecurity($data)
    {
        try {
            if (!$data) {
                return false;
            }
            if (isset($data['id'])) {
                $security = self::find($data['id']);
                $security->content = $data['content_1'];
                $security->description = $data['description_1'];
                $security->period = $data['period_1'];
                $security->procedure = $data['procedure_1'];
                $security->updated_at = Carbon::now()->format('Y-m-d H:i:s');
            } else {
                $security = new Security();
                $security->proj_id = $data['project_id'];
                $security->content = $data['content_1'];
                $security->description = $data['description_1'];
                $security->procedure = $data['procedure_1'];
                $security->period = $data['period_1'];
                $security->created_at = Carbon::now()->format('Y-m-d H:i:s');
                $security->updated_at = Carbon::now()->format('Y-m-d H:i:s');
            }
            $security->save();
            $memberOld = self::getAllMemberOfTraining($security->id);
            if ($memberOld) {
                //Delete old training member
                $security->securityMember()->detach($memberOld);
            }
            $security->securityMember()->attach($data['member_1']);
            DB::commit();
            CacheHelper::forget(self::KEY_CACHE_WO, $data['project_id']);
            return true;
        } catch (\Exception $ex) {
            dd($ex);
            DB::rollback();
            return false;
        }
    }

    public function securityMember() {
        $tableSecurityMember = SecurityMember::getTableName();
        return $this->belongsToMany('Rikkei\Team\Model\Employee', $tableSecurityMember, 'security_id', 'employee_id')->withTimestamps();
    }

    public static function getAllMemberOfSecurity($id)
    {
        $security = self::find($id);
        if (!$security) {
            return;
        }
        $members = array();
        foreach ($security->securityMember as $member) {
            array_push($members, $member->id);
        }
        return $members;
    }

    public static function deleteSecurity($data)
    {
        try {
            $security = self::find($data['id']);
            if (!isset($security)) {
                return false;
            }
            $security->deleted_at = Carbon::now()->format('Y-m-d H:i:s');
            $security->save();
            DB::commit();
            CacheHelper::forget(self::KEY_CACHE_WO, $data['project_id']);
            return true;
        } catch (\Exception $ex) {
            DB::rollback();
            return false;
        }
    }

    public static function getContentTable($project)
    {
        $permission = View::checkPermissionEditWorkorder($project);
        $permissionEdit = $permission['persissionEditPM'] || $permission['permissionEditSubPM'] || $permission['permissionEditPqa'];
        $checkEditWorkOrder = Task::checkEditWorkOrder($project->id);
        $allSecurity = self::getSecurity($project->id);
        return view('project::components.security', ['permissionEdit' => $permissionEdit, 'checkEditWorkOrder' => $checkEditWorkOrder, 'allSecurity' => $allSecurity, 'detail' => true])->render();
    }

    /**
     * get value attribute
     * @param int $id, int $attribute
     * @return string $attribute
     */
    public static function getValueAttribute($id, $attribute) {
        $value = self::find($id);
        if ($value) {
            return nl2br($value->$attribute);
        }
        return null;
    }
}
