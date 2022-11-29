<?php

namespace Rikkei\Project\Model;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Rikkei\Core\View\CacheHelper;
use Rikkei\Project\View\View;

class AssumptionsConstraints extends ProjectWOBase

{
    use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'proj_assumptions_constraints';

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
    protected $fillable = ['proj_id', 'remark', 'description', 'type'];

    public static function getAssumptions($id, $type)
    {
         return self::select('id', 'description', 'remark')
            ->where('type', $type)
            ->where('proj_id', $id)
            ->whereNull('deleted_at')
            ->get();
    }

    public static function insertAssumptions($data)
    {
        try {
            if (!$data) {
                return false;
            }
            if (isset($data['id'])) {
                $assumption = self::find($data['id']);
                $assumption->description = $data['description_1'];
                $assumption->remark = $data['remark_1'];
                $assumption->updated_at = Carbon::now()->format('Y-m-d H:i:s');
            } else {
                $assumption = new AssumptionsConstraints();
                $assumption->proj_id = $data['project_id'];
                $assumption->description = $data['description_1'];
                $assumption->remark = $data['remark_1'];
                $assumption->created_at = Carbon::now()->format('Y-m-d H:i:s');
                $assumption->updated_at = Carbon::now()->format('Y-m-d H:i:s');
                $assumption->type = $data['type'];
            }
            $assumption->save();
            DB::commit();
            CacheHelper::forget(self::KEY_CACHE_WO, $data['project_id']);
            return true;
        } catch (\Exception $ex) {
            DB::rollback();
            return false;
        }
    }

    public static function deleteAssumptions($data)
    {
        try {
            $assumption = self::find($data['id']);
            if (!isset($assumption)) {
                return false;
            }
            $assumption->deleted_at = Carbon::now()->format('Y-m-d H:i:s');
            $assumption->save();
            DB::commit();
            CacheHelper::forget(self::KEY_CACHE_WO, $data['project_id']);
            return true;
        } catch (\Exception $ex) {
            DB::rollback();
            return false;
        }
    }

    public static function getContentTable($project, $type)
    {
        $permission = View::checkPermissionEditWorkorder($project);
        $permissionEdit = $permission['persissionEditPM'] || $permission['permissionEditSubPM'] || $permission['permissionEditPqa'];
        $checkEditWorkOrder = Task::checkEditWorkOrder($project->id);
        $allAssumptions = self::getAssumptions($project->id, $type);
        if ($type == Task::TYPE_WO_ASSUMPTIONS) {
            return view('project::components.assumptions', ['permissionEdit' => $permissionEdit, 'checkEditWorkOrder' => $checkEditWorkOrder, 'allAssumptions' => $allAssumptions, 'detail' => true])->render();
        }
        return view('project::components.constraints', ['permissionEdit' => $permissionEdit, 'checkEditWorkOrder' => $checkEditWorkOrder, 'allConstraints' => $allAssumptions, 'detail' => true])->render();
    }

    /**
     * get value attribute
     * @param int $id, int $attribute
     * @return string $attribute
     */
    public static function getValueAttribute($id, $attribute) {
        $value = self::find($id);
        if ($value) {
            if (in_array($attribute, ['description_assumptions', 'description_constraints'])) {
                return nl2br($value->description);
            } else {
                return nl2br($value->remark);
            }
        }
        return null;
    }
}
