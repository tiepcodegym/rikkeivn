<?php

namespace Rikkei\Project\Model;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Rikkei\Core\View\CacheHelper;
use Rikkei\Project\View\View;

class CommunicationProject extends ProjectWOBase

{
    use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'proj_communication';

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
    protected $fillable = ['proj_id', 'type', 'method', 'time', 'information', 'stakeholder', 'type_task'];

    public static function getCommunication($id, $type)
    {
        return self::select('id', 'proj_id', 'type', 'method', 'time', 'information', 'stakeholder', 'type_task')
            ->where('proj_id', $id)
            ->where('type_task', $type)
            ->whereNull('deleted_at')
            ->get();
    }

    public static function insertCommunication($data)
    {
        try {
            if (!$data) {
                return false;
            }
            if (isset($data['id'])) {
                $communication = self::find($data['id']);
                $communication->type = $data['type_1'];
                $communication->method = $data['method_1'];
                $communication->time = $data['time_1'];
                $communication->information = $data['information_1'];
                $communication->stakeholder = $data['stakeholder_1'];
                $communication->type_task = $data['type_task'];
                $communication->updated_at = Carbon::now()->format('Y-m-d H:i:s');
            } else {
                $communication = new CommunicationProject();
                $communication->proj_id = $data['project_id'];
                $communication->type = $data['type_1'];
                $communication->method = $data['method_1'];
                $communication->time = $data['time_1'];
                $communication->information = $data['information_1'];
                $communication->stakeholder = $data['stakeholder_1'];
                $communication->type_task = $data['type_task'];
                $communication->created_at = Carbon::now()->format('Y-m-d H:i:s');
                $communication->updated_at = Carbon::now()->format('Y-m-d H:i:s');
            }
            $communication->save();
            DB::commit();
            CacheHelper::forget(self::KEY_CACHE_WO, $data['project_id']);
            return true;
        } catch (\Exception $ex) {
            DB::rollback();
            return false;
        }
    }

    public static function deleteCommunication($data)
    {
        try {
            $communication = self::find($data['id']);
            if (!isset($communication)) {
                return false;
            }
            $communication->deleted_at = Carbon::now()->format('Y-m-d H:i:s');
            $communication->save();
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
        $communicationMeeting = CommunicationProject::getCommunication($project->id, Task::TYPE_WO_MEETING_COMMUNICATION);
        $communicationReport = CommunicationProject::getCommunication($project->id, Task::TYPE_WO_REPORT_COMMUNICATION);
        $communicationOther = CommunicationProject::getCommunication($project->id, Task::TYPE_WO_OTHER_COMMUNICATION);
        return view('project::components.communication', ['permissionEdit' => $permissionEdit, 'checkEditWorkOrder' => $checkEditWorkOrder, 'project' => $project,
            'communicationMeeting' => $communicationMeeting, 'communicationReport' => $communicationReport, 'communicationOther' => $communicationOther, 'detail' => true])->render();
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

    public static function syncExample($projId)
    {
        try {
            $member = [];
            if ($projId) {
                $member[0] = [
                    'proj_id' => $projId,
                    'type' => trans('project::view.Kickoff meeting'),
                    'method' => trans('project::view.Meeting'),
                    'time' => '',
                    'information' => trans('project::view.information meeting_1'),
                    'stakeholder' => trans('project::view.stakeholder meeting_1'),
                    'type_task' => Task::TYPE_WO_MEETING_COMMUNICATION,
                ];
                $member[1] = [
                    'proj_id' => $projId,
                    'type' => trans('project::view.Meeting review progress'),
                    'method' => trans('project::view.Face to face'),
                    'time' => '',
                    'information' => trans('project::view.information meeting_2'),
                    'stakeholder' => trans('project::view.stakeholder meeting_2'),
                    'type_task' => Task::TYPE_WO_MEETING_COMMUNICATION,
                ];
                $member[2] = [
                    'proj_id' => $projId,
                    'type' => trans('project::view.Milestone meeting'),
                    'method' => trans('project::view.Face to face'),
                    'time' => trans('project::view.time example meeting_1'),
                    'information' => trans('project::view.information meeting_3'),
                    'stakeholder' => '',
                    'type_task' => Task::TYPE_WO_MEETING_COMMUNICATION,
                ];
                $member[3] = [
                    'proj_id' => $projId,
                    'type' => trans('project::view.Project Post-mortem  Meeting'),
                    'method' => trans('project::view.Face to face'),
                    'time' => trans('project::view.time example meeting_2'),
                    'information' => trans('project::view.information meeting_4'),
                    'stakeholder' => trans('project::view.stakeholder meeting_1'),
                    'type_task' => Task::TYPE_WO_MEETING_COMMUNICATION,
                ];
                $member[4] = [
                    'proj_id' => $projId,
                    'type' => trans('project::view.Transfer/Sharing of project documentation/information'),
                    'method' => trans('project::view.Shared Project Repository/FTP/CVS/MS Share Point Server'),
                    'time' => trans('project::view.time example meeting_3'),
                    'information' => trans('project::view.information meeting_5'),
                    'stakeholder' => trans('project::view.stakeholder meeting_1'),
                    'type_task' => Task::TYPE_WO_MEETING_COMMUNICATION,
                ];
                $member[5] = [
                    'proj_id' => $projId,
                    'type' => trans('project::view.Project report'),
                    'method' => trans('project::view.E mail'),
                    'time' => trans('project::view.time example meeting_5'),
                    'information' => trans('project::view.information meeting_6'),
                    'stakeholder' => '',
                    'type_task' => Task::TYPE_WO_REPORT_COMMUNICATION,
                ];
                $member[6] = [
                    'proj_id' => $projId,
                    'type' => trans('project::view.Weekly meeting'),
                    'method' => trans('project::view.Meeting'),
                    'time' => trans('project::view.time example meeting_5'),
                    'information' => trans('project::view.information meeting_7'),
                    'stakeholder' => '',
                    'type_task' => Task::TYPE_WO_REPORT_COMMUNICATION,
                ];
                $member[7] = [
                    'proj_id' => $projId,
                    'type' => trans('project::view.Project Meeting with customer'),
                    'method' => trans('project::view.E mail ・ meeting'),
                    'time' => '',
                    'information' => trans('project::view.information meeting_8'),
                    'stakeholder' => '',
                    'type_task' => Task::TYPE_WO_REPORT_COMMUNICATION,
                ];
                $member[8] = [
                    'proj_id' => $projId,
                    'type' => trans('project::view.Meeting clear spec'),
                    'method' => trans('project::view.E mail ・ meeting'),
                    'time' => '',
                    'information' => trans('project::view.information meeting_8'),
                    'stakeholder' => trans('project::view.stakeholder meeting_3'),
                    'type_task' => Task::TYPE_WO_REPORT_COMMUNICATION,
                ];
                $member[9] = [
                    'proj_id' => $projId,
                    'type' => trans('project::view.Review Project Plan & Project schedule'),
                    'method' => trans('project::view.By email or attend project meeting'),
                    'time' => trans('project::view.time example meeting_4'),
                    'information' => '',
                    'stakeholder' => trans('project::view.stakeholder meeting_4'),
                    'type_task' => Task::TYPE_WO_OTHER_COMMUNICATION,
                ];
                self::insert($member);
                DB::commit();
            }
        } catch (\Exception $ex) {
            DB::rollback();
            return false;
        }
    }
}
