<?php

namespace Rikkei\Project\Model;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Rikkei\Core\View\CacheHelper;
use Rikkei\Project\View\View;

class SkillRequest extends ProjectWOBase

{
    use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'proj_op_skill_request';

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
    protected $fillable = ['proj_id', 'remark', 'skill', 'category', 'course_name', 'mode', 'provider', 'required_for_role', 'hours', 'level_assessment_method'];

    public static function getSkillRequest($id)
    {
         return self::select('id', 'remark', 'skill', 'category', 'course_name', 'mode', 'provider', 'required_for_role', 'hours', 'level_assessment_method')
            ->where('proj_id', $id)
            ->whereNull('deleted_at')
            ->get();
    }

    public static function insertSkillRequest($data)
    {
        try {
            if (!$data) {
                return false;
            }
            if (isset($data['id'])) {
                $skill = self::find($data['id']);
                $skill->skill = $data['skill_1'];
                $skill->remark = $data['remark_1'];
                $skill->category = $data['category_1'];
                $skill->course_name = $data['course_name_1'];
                $skill->mode = $data['mode_1'];
                $skill->provider = $data['provider_1'];
                $skill->required_for_role = $data['required_role_1'];
                $skill->hours = $data['hours_1'];
                $skill->level_assessment_method = $data['level_1'];
                $skill->updated_at = Carbon::now()->format('Y-m-d H:i:s');
            } else {
                $skill = new SkillRequest();
                $skill->proj_id = $data['project_id'];
                $skill->skill = $data['skill_1'];
                $skill->remark = $data['remark_1'];
                $skill->category = $data['category_1'];
                $skill->course_name = $data['course_name_1'];
                $skill->mode = $data['mode_1'];
                $skill->provider = $data['provider_1'];
                $skill->required_for_role = $data['required_role_1'];
                $skill->hours = $data['hours_1'];
                $skill->level_assessment_method = $data['level_1'];
                $skill->created_at = Carbon::now()->format('Y-m-d H:i:s');
                $skill->updated_at = Carbon::now()->format('Y-m-d H:i:s');
            }
            $skill->save();
            DB::commit();
            CacheHelper::forget(self::KEY_CACHE_WO, $data['project_id']);
            return true;
        } catch (\Exception $ex) {
            dd($ex);
            DB::rollback();
            return false;
        }
    }

    public static function deleteSkillRequest($data)
    {
        try {
            $skill = self::find($data['id']);
            if (!isset($skill)) {
                return false;
            }
            $skill->deleted_at = Carbon::now()->format('Y-m-d H:i:s');
            $skill->save();
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
        $allSkillRequest = self::getSkillRequest($project->id);
        return view('project::components.skills-request', ['permissionEdit' => $permissionEdit, 'checkEditWorkOrder' => $checkEditWorkOrder, 'getSkillRequest' => $allSkillRequest, 'detail' => true])->render();
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
