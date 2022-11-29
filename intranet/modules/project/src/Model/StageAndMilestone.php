<?php

namespace Rikkei\Project\Model;

use Rikkei\Team\View\Permission;
use Illuminate\Database\Eloquent\SoftDeletes;
use Rikkei\Project\Model\Task;
use Rikkei\Core\View\CacheHelper;
use Illuminate\Support\Facades\DB;
use Lang;
use Rikkei\Project\View\View;
use Exception;
use Carbon\Carbon;

class StageAndMilestone extends ProjectWOBase
{
    use SoftDeletes;
    
    const KEY_CACHE_SAM = 'proj_op_stages';
    
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'proj_op_stages';

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
    protected $fillable = ['project_id', 'stage', 'description',
                            'milestone','status', 'type',
                            'state','start_at', 'type'];

    const QUALITY_GATE_RESULT_PASS = 1;
    const QUALITY_GATE_RESULT_FAIL = 0;

    /**
     *  define stage  
     */
    const STAGE_INITIATION = 1;
    const STAGE_DEFINITION = 2;
    const STAGE_SOLUTION = 3;
    const STAGE_CONSTRUCTION = 4;
    const STAGE_TRANSITION = 5;
    const STAGE_TERMINATION = 6;
    /**
     * Get the stage and milestone child
     */
    public function projectStageAndMilestoneChild() {
        return $this->hasOne('Rikkei\Project\Model\StageAndMilestone', 'parent_id');
    }

    /**
     * count the stage deliverable
     * @param int
     * @param int
     * @return int
     */
    public function projectDeliverable($stageId, $projectId) {
        return ProjDeliverable::where('stage_id', $stageId)
                               ->where('project_id', $projectId)->count();
    }

    /**
     * get all stage define
     * @return array
     */
    public static function getAllStage()
    {
        return [
            self::STAGE_INITIATION => 'Initiation',
            self::STAGE_DEFINITION => 'Definition',
            self::STAGE_SOLUTION => 'Solution',
            self::STAGE_CONSTRUCTION => 'Construction',
            self::STAGE_TRANSITION => 'Transition',
            self::STAGE_TERMINATION => 'Termination',
        ];
    }
    
    /**
     * get stage title name follow flag id
     * 
     * @param type $stageId
     * @param array $stagesLabel
     * @return string
     */
    public static function getStageTitle($stageId, array $stagesLabel = [])
    {
        if (!$stagesLabel) {
            $stagesLabel = self::getAllStage();
        }
        if (isset($stagesLabel[$stageId])) {
            return $stagesLabel[$stageId];
        }
        return null;
    }
    
    /*
     * get all stage and milestone by project id
     * @param int
     * @return collection
     */
    public static function getAllStageAndMilestone($projectId)
    {
        $collection = self::select(['id', 'stage', 'description', 'milestone', 'status', 'qua_gate_plan', 'parent_id'])
                            ->where('project_id', $projectId)
                            ->orderBy('created_at', 'asc')
                            ->get();
        if (!count($collection)) {
            return null;
        }
        $result = [];

        foreach ($collection as $key => $item) {
            if (View::checkItemIsParent($item->status)) {
                $result[$item->id]['parent'] = $item;
            } else {
                $result[$item->parent_id]['child'] = $item;
            }
        }
        return $result;
    }

    /*
     * add stage and milestone
     * @param array
     */
    public static function insertStageAndMilestone($input)
    {
        DB::beginTransaction();
        try {
            $arrayLablelStatus = self::getLabelStatusForProjectLog();
            $labelElement = self::getLabelElementForWorkorder()[Task::TYPE_WO_STAGE_MILESTONE];
            if (config('project.workorder_approved.stage_and_milestone')) {
                if (isset($input['isAddNew'])) {
                    $stageAndMilestone = new StageAndMilestone();
                    $stageAndMilestone->project_id = $input['project_id'];
                    $stageAndMilestone->status = self::STATUS_DRAFT;
                    $stageAndMilestone->stage = $input['stage_1'];
                    $stageAndMilestone->description = $input['description_1'];
                    $stageAndMilestone->milestone = $input['milestone_1'];
                    $stageAndMilestone->qua_gate_plan = $input['qua_gate_plan_1'];
                    $stageAndMilestone->created_by = Permission::getInstance()->getEmployee()->id;
                    $status = self::STATUS_DRAFT;
                    $stageAndMilestone->save();
                } else if (isset($input['isEdit'])) {
                    if (isset($input['status']) && $input['status'] == self::STATUS_APPROVED) {
                        $stageAndMilestone = StageAndMilestone::where('parent_id', $input['id'])->first();
                        $stage = StageAndMilestone::find($input['id']);
                        if ($stageAndMilestone) {
                            $stageAndMilestoneOrigin = $stageAndMilestone->original;
                            $stageAndMilestone->stage = $input['stage_1'];
                            $stageAndMilestone->description = $input['description_1'];
                            $stageAndMilestone->milestone = $input['milestone_1'];
                            $stageAndMilestone->qua_gate_plan = $input['qua_gate_plan_1'];
                            $stageAndMilestoneAttributes = $stageAndMilestone->attributes;
                            $status = self::STATUS_EDIT_APPROVED;
                            $isChangeValue = View::isChangeValue($stage, $stageAndMilestone);
                            if (!$isChangeValue) {
                                if ($stageAndMilestone->status == self::STATUS_DRAFT_EDIT) {
                                    $stageAndMilestone->forceDelete();
                                } else {
                                    $stageAndMilestone->status = self::STATUS_FEEDBACK_DELETE;
                                    $stageAndMilestone->save();
                                }
                                DB::commit();
                                CacheHelper::forget(self::KEY_CACHE_WO, $input['project_id']);
                                return true;
                            }
                        } else {
                            $stageAndMilestone = new StageAndMilestone();
                            $stageAndMilestone->stage = $input['stage_1'];
                            $stageAndMilestone->description = $input['description_1'];
                            $stageAndMilestone->milestone = $input['milestone_1'];
                            $stageAndMilestone->qua_gate_plan = $input['qua_gate_plan_1'];
                            $isChangeValue = View::isChangeValue($stage, $stageAndMilestone);
                            $stageAndMilestoneAttributes = $stageAndMilestone->attributes;
                            $stageAndMilestoneOrigin = $stageAndMilestone->original;
                            if($isChangeValue) {
                                $status = self::STATUS_EDIT_APPROVED;
                                $stageAndMilestone->project_id = $input['project_id'];
                                $stageAndMilestone->status = self::STATUS_DRAFT_EDIT;
                                $stageAndMilestone->parent_id = $input['id'];
                                $stageAndMilestone->created_by = Permission::getInstance()->getEmployee()->id;
                            } else {
                                return true;
                            }
                        }
                    } else {
                        $stageAndMilestone = self::find($input['id']);
                        switch ($stageAndMilestone->status) {
                            case self::STATUS_FEEDBACK_EDIT:
                                $status = self::STATUS_FEEDBACK_EDIT;
                                $stageAndMilestone->status = self::STATUS_DRAFT_EDIT;
                                break;
                            case self::STATUS_FEEDBACK:
                                $status = self::STATUS_FEEDBACK;
                            $stageAndMilestone->status = self::STATUS_DRAFT;
                                break;
                            case self::STATUS_DRAFT_EDIT:
                                $status = self::STATUS_DRAFT_EDIT;
                                break;
                            case self::STATUS_DRAFT:
                                $status = self::STATUS_UPDATED_DRAFT;
                                break;
                            default:
                                $status = self::STATUS_EDIT_APPROVED;
                                break;
                        }
                        if ($stageAndMilestone->parent_id) {
                            $stage = StageAndMilestone::find($stageAndMilestone->parent_id);
                        } else {
                            $isChange = false;
                        }
                        $stageAndMilestoneOrigin = $stageAndMilestone->original;
                        $stageAndMilestone->stage = $input['stage_1'];
                        $stageAndMilestone->description = $input['description_1'];
                        $stageAndMilestone->milestone = $input['milestone_1'];
                        $stageAndMilestone->qua_gate_plan = $input['qua_gate_plan_1'];
                        $stageAndMilestoneAttributes = $stageAndMilestone->attributes;
                    }
                    if (!isset($isChange)) {
                        if(!View::isChangeValue($stage, $stageAndMilestone)) {
                            if ($stageAndMilestone->status == self::STATUS_DRAFT_EDIT && isset($stageAndMilestone->id)) {
                                $stageAndMilestone->forceDelete();
                                DB::commit();
                                CacheHelper::forget(self::KEY_CACHE_WO, $input['project_id']);
                                return true;
                            }
                        }
                    }
                    $stageAndMilestone->save();
                }
                $statusText = $arrayLablelStatus[$status];
                if ($status == self::STATUS_DRAFT || $status == self::STATUS_EDIT_APPROVED) {
                    View::insertProjectLogWO($input['project_id'], $statusText, $labelElement, null, null);
                } else {
                    View::insertProjectLogWO($input['project_id'], $statusText, $labelElement, $stageAndMilestoneAttributes, $stageAndMilestoneOrigin);
                }
            } else {
                if (isset($input['id'])) {
                    $status = self::STATUS_EDIT;
                    $stageAndMilestone = self::find($input['id']);
                } else {
                    $status = self::STATUS_ADD;
                    $stageAndMilestone = new StageAndMilestone;
                    $stageAndMilestone->project_id = $input['project_id'];
                    $stageAndMilestone->created_by = Permission::getInstance()->getEmployee()->id;
                    $stageAndMilestone->status = self::STATUS_APPROVED;
                }
                $stageAndMilestone->stage = $input['stage_1'];
                $stageAndMilestone->description = $input['description_1'];
                $stageAndMilestone->milestone = $input['milestone_1'];

                $stageAndMilestone->qua_gate_plan = $input['qua_gate_plan_1'];
                $stageAndMilestoneAttributes = $stageAndMilestone->attributes;
                $stageAndMilestoneOrigin = $stageAndMilestone->original;
                $stageAndMilestone->save();
                $statusText = $arrayLablelStatus[$status];
                if ($status == self::STATUS_ADD) {
                    View::insertProjectLogWO($input['project_id'], $statusText, $labelElement, null, null);
                } else {
                    View::insertProjectLogWO($input['project_id'], $statusText, $labelElement, $stageAndMilestoneAttributes, $stageAndMilestoneOrigin);
                }
            }
            DB::commit();
            CacheHelper::forget(self::KEY_CACHE_WO, $input['project_id']);
            return $stageAndMilestone->id;
        } catch (Exception $ex) {
            DB::rollback();
            return false;
        }
    }                

    /*
     * delete stage and milestone
     * @param array
     * @return boolean
     */
    public static function deleteStageAndMilestone($input)
    {
        $arrayLablelStatus = self::getLabelStatusForProjectLog();
        $labelElement = self::getLabelElementForWorkorder()[Task::TYPE_WO_STAGE_MILESTONE];
        if (config('project.workorder_approved.stage_and_milestone')) {
            $stageAndMilestone = self::find($input['id']);
            if ($stageAndMilestone) {
                if($stageAndMilestone->status == self::STATUS_APPROVED) {
                    $stageAndMilestoneDelete = $stageAndMilestone->replicate();
                    $stageAndMilestoneDelete->status = self::STATUS_DRAFT_DELETE;
                    $stageAndMilestoneDelete->parent_id = $input['id'];
                    $status = self::STATUS_DELETE_APPROVED;
                    if ($stageAndMilestoneDelete->save()) {
                        CacheHelper::forget(self::KEY_CACHE_WO, $input['project_id']);
                        $statusText = $arrayLablelStatus[$status];
                        View::insertProjectLogWO($input['project_id'], $statusText, $labelElement, null, null);
                        return true;
                    }
                } else {
                    switch ($stageAndMilestone->status) {
                        case self::STATUS_DRAFT_EDIT:
                            $status = self::STATUS_DELETE_DRAFT_EDIT;
                            break;
                        case self::STATUS_DRAFT:
                            $status = self::STATUS_DELETE_DRAFT;
                            break;
                        case self::STATUS_FEEDBACK_DELETE:
                            $status = self::STATUS_FEEDBACK_DELETE;
                            break;
                        case self::STATUS_FEEDBACK_EDIT:
                            $status = self::STATUS_DELETE_FEEDBACK_EDIT;
                            break;
                        case self::STATUS_FEEDBACK:
                            $status = self::STATUS_DELETE_FEEDBACK;
                            break;
                        default:
                            $status = self::STATUS_DRAFT_DELETE;
                            break;
                    }
                    if ($stageAndMilestone->delete()) {
                        CacheHelper::forget(self::KEY_CACHE_WO, $input['project_id']);
                        $statusText = $arrayLablelStatus[$status];
                        View::insertProjectLogWO($input['project_id'], $statusText, $labelElement, null, null);
                        return true;
                    } 
                }
            }
        } else {
            $status = self::STATUS_DELETE;
            $stageAndMilestone = self::find($input['id']);
            if ($stageAndMilestone) {
                if ($stageAndMilestone->delete()) {
                    CacheHelper::forget(self::KEY_CACHE_WO, $input['project_id']);
                    $statusText = $arrayLablelStatus[$status];
                    View::insertProjectLogWO($input['project_id'], $statusText, $labelElement, null, null);
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * get content task approved
     * @param int
     * @param array
     * @return string
     *
     */
    public static function getContentTaskApproved($typeWO, $input, $content, $typeSubmit = null)
    {
        $stageAndMilestoneDraft = self::where('project_id', $input['project_id']);
        if ($typeSubmit) {
            $stageAndMilestoneDraft = $stageAndMilestoneDraft->whereIn('status', [self::STATUS_DRAFT, self::STATUS_FEEDBACK]);
        } else {
            $stageAndMilestoneDraft = $stageAndMilestoneDraft->where('status', self::STATUS_DRAFT);
        }
        $stageAndMilestoneDraft = $stageAndMilestoneDraft->get();
        if(count($stageAndMilestoneDraft) > 0) {
            $title = Lang::get('project::view.Add object for Project stages and milestones');
            $content .= view('project::template.content-task', ['inputs' => $stageAndMilestoneDraft, 'title' => $title, 'type' => $typeWO])->render();
        }

        $stageAndMilestoneDraftEdit = self::where('project_id', $input['project_id']);
        if ($typeSubmit) {
            $stageAndMilestoneDraftEdit = $stageAndMilestoneDraftEdit->whereIn('status', [self::STATUS_DRAFT_EDIT, self::STATUS_FEEDBACK_EDIT]);
        } else {
            $stageAndMilestoneDraftEdit = $stageAndMilestoneDraftEdit->where('status', self::STATUS_DRAFT_EDIT);
        }
        $stageAndMilestoneDraftEdit = $stageAndMilestoneDraftEdit->get();
        if(count($stageAndMilestoneDraftEdit) > 0) {
            $title = Lang::get('project::view.Edit object for Project stages and milestones');
            $content .= view('project::template.content-task', ['inputs' => $stageAndMilestoneDraftEdit, 'title' => $title, 'type' => $typeWO])->render();
        }

        $stageAndMilestoneDelete = self::where('project_id', $input['project_id']);
        if ($typeSubmit) {
            $stageAndMilestoneDelete = $stageAndMilestoneDelete->whereIn('status', [self::STATUS_DRAFT_DELETE, self::STATUS_FEEDBACK_DELETE]);
        } else {
            $stageAndMilestoneDelete = $stageAndMilestoneDelete->where('status', self::STATUS_DRAFT_DELETE);
        }
        $stageAndMilestoneDelete = $stageAndMilestoneDelete->get();
        if(count($stageAndMilestoneDelete) > 0) {
            $title = Lang::get('project::view.Delete object for Project stages and milestones');
            $content .= view('project::template.content-task', ['inputs' => $stageAndMilestoneDelete, 'title' => $title, 'type' => $typeWO])->render();
        }
        return $content; 
    }
    
    /**
     * check status submit
     * @param int
     * @return boolean
     */
    public static function checkStatusSubmit($projectId)
    {
        $status = false;
        if ($checkStatus = CacheHelper::get(self::KEY_CACHE_WO, $projectId)) {
            return $checkStatus;
        }
        $items = self::where('project_id', $projectId)
                       ->whereIn('status', [self::STATUS_DRAFT, self::STATUS_DRAFT_EDIT, self::STATUS_DRAFT_DELETE])->count();
        if ($items > 0) {
            $status = true;
        }
        CacheHelper::put(self::KEY_CACHE_WO, $status, $projectId);
        return $status;
    }

    /**
     * update status when submit workorder
     * @param array
     */
    public static function updateStatusWhenSubmitWorkorder($task, $input)
    {
        $stageDraft = self::where('project_id', $input['project_id'])
                                    ->whereIn('status', [self::STATUS_DRAFT, self::STATUS_FEEDBACK])
                                    ->get();
        if(count($stageDraft) > 0) {
            foreach($stageDraft as $stage) {
                $stage->status = self::STATUS_SUBMITTED;
                $stage->task_id = $task->id;
                $stage->save();
            }
        }

        $stageEdit = self::where('project_id', $input['project_id'])
                                        ->whereIn('status', [self::STATUS_DRAFT_EDIT, self::STATUS_FEEDBACK_EDIT])
                                        ->whereNotNull('parent_id')
                                        ->get();
        if(count($stageEdit) > 0) {
            foreach($stageEdit as $stage) {
                $stage->status = self::STATUS_SUBMIITED_EDIT;
                $stage->task_id = $task->id;
                $stage->save();
            }
        }

        $stageDelete = self::where('project_id', $input['project_id'])
                                    ->whereIn('status', [self::STATUS_DRAFT_DELETE, self::STATUS_FEEDBACK_DELETE])
                                    ->get();
        if(count($stageDelete)) {
            foreach($stageDelete as $stage) {
                $stage->status = self::STATUS_SUBMMITED_DELETE;
                $stage->task_id = $task->id;
                $stage->save();
            }
        }
        
        CacheHelper::forget(self::KEY_CACHE_WO, $input['project_id']);
    }

    /**
     * update status when submit slove workorder
     * @param array
     */
    public static function updateStatusWhenSloveWorkorder($statusTask, $projectId)
    {   
        self::updateItemWorkorder(self::TYPE_ADD, $statusTask, $projectId);
        self::updateItemWorkorder(self::TYPE_EDIT, $statusTask, $projectId);
        self::updateItemWorkorder(self::TYPE_DELETE, $statusTask, $projectId);
        CacheHelper::forget(self::KEY_CACHE_WO, $projectId);
    }

    /**
     * update itemt workorder
     * @param int
     * @param int
     * @param int
     */
    public static function updateItemWorkorder($type, $statusTask, $projectId)
    {
        $stageAndMilestoneDraft = self::where('project_id', $projectId);
        if ($statusTask == Task::STATUS_APPROVED) {
            if ($type == self::TYPE_ADD) {
                $stageAndMilestoneDraft = $stageAndMilestoneDraft->where('status', self::STATUS_REVIEWED);
            } else if($type == self::TYPE_EDIT) {
                $stageAndMilestoneDraft = $stageAndMilestoneDraft->where('status', self::STATUS_REVIEWED_EDIT);
            } else {
                $stageAndMilestoneDraft = $stageAndMilestoneDraft->where('status', self::STATUS_REVIEWED_DELETE);
            }
        } else if ($statusTask == Task::STATUS_FEEDBACK || $statusTask == Task::STATUS_REVIEWED) {
            if ($type == self::TYPE_ADD) {
                $stageAndMilestoneDraft = $stageAndMilestoneDraft->whereIn('status', [self::STATUS_SUBMITTED, self::STATUS_REVIEWED]);
            } else if($type == self::TYPE_EDIT) {
                $stageAndMilestoneDraft = $stageAndMilestoneDraft->whereIn('status', [self::STATUS_SUBMIITED_EDIT, self::STATUS_REVIEWED_EDIT]);
            } else {
                $stageAndMilestoneDraft = $stageAndMilestoneDraft->whereIn('status', [self::STATUS_SUBMMITED_DELETE, self::STATUS_REVIEWED_DELETE]);
            }
        }
        $stageAndMilestoneDraft = $stageAndMilestoneDraft->get();
        if(count($stageAndMilestoneDraft) > 0) {
            if ($statusTask == Task::STATUS_APPROVED) {
                if ($type == self::TYPE_DELETE) {
                    foreach($stageAndMilestoneDraft as $stageAndMilestone) {
                        $stageAndMilestoneParent = self::find($stageAndMilestone->parent_id);
                        $stageAndMilestone->delete();
                        if($stageAndMilestoneParent) {
                            $stageAndMilestoneParent->delete();
                        }
                    }
                } else if($type == self::TYPE_ADD) {
                    foreach($stageAndMilestoneDraft as $stageAndMilestone) {
                        $stageAndMilestone->status = self::STATUS_APPROVED;
                        $stageAndMilestone->save();
                    }
                } else { // item edit
                    foreach($stageAndMilestoneDraft as $stageAndMilestone) {
                        $stageAndMilestoneParent = self::find($stageAndMilestone->parent_id);

                        if($stageAndMilestoneParent) {
                            View::updateValueWhenApproved($stageAndMilestoneParent, $stageAndMilestone);
                        }
                    }
                }
            } else if ($statusTask == Task::STATUS_REVIEWED) {
                foreach($stageAndMilestoneDraft as $stageAndMilestone) {
                    if ($stageAndMilestone->status == self::STATUS_SUBMITTED) {
                        $stageAndMilestone->status = self::STATUS_REVIEWED;
                    }
                    if ($stageAndMilestone->status == self::STATUS_SUBMIITED_EDIT) {
                        $stageAndMilestone->status = self::STATUS_REVIEWED_EDIT;
                    }
                    if ($stageAndMilestone->status == self::STATUS_SUBMMITED_DELETE) {
                        $stageAndMilestone->status = self::STATUS_REVIEWED_DELETE;
                    }
                    $stageAndMilestone->save();
                }
            } else if ($statusTask == Task::STATUS_FEEDBACK) {
                foreach($stageAndMilestoneDraft as $stageAndMilestone) {
                    if ($stageAndMilestone->status == self::STATUS_SUBMITTED ||
                        $stageAndMilestone->status == self::STATUS_REVIEWED) {
                        $stageAndMilestone->status = self::STATUS_FEEDBACK;
                    }
                    if ($stageAndMilestone->status == self::STATUS_SUBMIITED_EDIT ||
                        $stageAndMilestone->status == self::STATUS_REVIEWED_EDIT) {
                        $stageAndMilestone->status = self::STATUS_FEEDBACK_EDIT;
                    }
                    if ($stageAndMilestone->status == self::STATUS_SUBMMITED_DELETE ||
                        $stageAndMilestone->status == self::STATUS_REVIEWED_DELETE) {
                        $stageAndMilestone->status = self::STATUS_FEEDBACK_DELETE;
                    }
                    $stageAndMilestone->save();
                }
            }
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
        $allStagesAndMilestones = self::getAllStageAndMilestone($project->id);
        $allStage = self::getAllStage();
        return view('project::components.stages-milestones', ['permissionEdit' => $permissionEdit, 'checkEditWorkOrder' => $checkEditWorkOrder, 'allStagesAndMilestones' => $allStagesAndMilestones, 'detail' => true, 'allStage' => $allStage, 'project' => $project])->render();
    }
    
    /**
     * get actual date of quality gate
     * 
     * @param type $projectId
     * @return object Carbon Datetime
     */
    public static function getFirstQualityGateActual($projectId)
    {
        if ($item = CacheHelper::get(self::KEY_CACHE_SAM, $projectId)) {
            return $item;
        }
        $item = self::select(DB::raw('MIN(qua_gate_actual) as actual_date'))
            ->where('project_id', $projectId)
            ->where('status', self::STATUS_APPROVED)
            ->first();
        if (!$item || !$item->actual_date) {
            return null;
        }
        $item = Carbon::parse($item->actual_date);
        CacheHelper::put(self::KEY_CACHE_SAM, $item, $projectId);
        return $item;
    }
    
    /**
     * rewrite save model
     * 
     * @param array $options
     */
    public function save(array $options = array()) {
        try {
            CacheHelper::forget(self::KEY_CACHE_SAM, $this->project_id);
            $result = parent::save($options);
            $project = Project::find($this->project_id);
            ProjPointFlat::flatItemProject($project);
            return $result;
        } catch (Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * rewrite delete model
     */
    public function delete() {
        try {
            CacheHelper::forget(self::KEY_CACHE_SAM, $this->project_id);
            $result = parent::delete();
            $project = Project::find($this->project_id);
            ProjPointFlat::flatItemProject($project);
            return $result;
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    public static function getArrayStageOfProject($projectId)
    {
        return self::where('project_id', $projectId)->lists('id')->toArray();
    }

    /*
     * get stage and milestone by project id
     * @param int
     * @return model
     */
    public static function getStageAndMilestoneOfProject($projectId)
    {
        return self::where('project_id', $projectId)
                    ->whereNotIn('status', [self::STATUS_DRAFT_DELETE, self::STATUS_SUBMMITED_DELETE, self::STATUS_REVIEWED_DELETE, self::STATUS_FEEDBACK_DELETE]);
    }

    /**
     * check error time where submit workorder
     * @param int
     * @param array
     * @return boolean
     */
    public static function checkErrorTime($projectId, $projectDraft)
    {
        $stages = self::where(function ($query) use ($projectDraft) {
                        $query->orWhereDate('qua_gate_plan', '<', $projectDraft->start_at)
                          ->orWhereDate('qua_gate_plan', '>', $projectDraft->end_at);
                    })
                    ->where('project_id', $projectId)
                    ->get();
        $checkError = false;
        foreach($stages as $stage) {
            if ($stage->projectStageAndMilestoneChild) {
                continue;
            }
            $checkError = true;
            break;
        }
        return $checkError;
    }

    /**
     * check error with deliver
     * 
     * @param int $projectId
     * @return boolean
     */
    public static function checkErrorWithDeliverable($projectId)
    {
        $checkError = false;
        $stageOfProject = self::getStageAndMilestoneOfProject($projectId)->lists('id')->toArray();
        $allDeliverableCurrent = ProjDeliverable::getDeliverableCurrent($projectId);
        foreach($allDeliverableCurrent as $key => $deliverable) {
            if($deliverable->projectDeliverableChild) {
                continue;
            } else {
                if (!in_array($deliverable->stage_id, $stageOfProject)) {
                    $checkError = true;
                    break;
                }
            }
        }
        return $checkError;
    }
    
    /**
     * get content change after submit
     *  object multi item change
     * 
     * @param $projectId int
     * @param $type type of object change
     * @return array
     */
    public static function getChangesAfterSubmit($projectId, $type = null)
    {
        $result = [];
        $stagesLabel = self::getAllStage();
        $tableStage = self::getTableName();
        $selectCollection = [
            'id',
            'project_id',
            'stage',
            'description',
            'milestone',
            DB::raw("DATE({$tableStage}.`qua_gate_plan`) as `qua_gate_plan`"),
            'parent_id'
        ];
        // add items
        $collection = self::select($selectCollection)->whereIn('status',
            [self::STATUS_DRAFT, self::STATUS_FEEDBACK, self::STATUS_SUBMITTED,])
            ->where('project_id', $projectId)
            ->get();
        if (count($collection)) {
            $result[TaskWoChange::FLAG_STAGE][TaskWoChange::FLAG_STATUS_ADD] = 
                self::toArrayLabelStage($collection, $stagesLabel); 
        }
        
        // delete item
        $collection = self::select($selectCollection)->whereIn('status',
            [self::STATUS_DRAFT_DELETE, self::STATUS_FEEDBACK_DELETE, self::STATUS_SUBMMITED_DELETE])
            ->where('project_id', $projectId)
            ->get();
        if (count($collection)) {
            $result[TaskWoChange::FLAG_STAGE][TaskWoChange::FLAG_STATUS_DELETE] 
                = self::toArrayLabelStage($collection, $stagesLabel);
        }
        
        // edit item
        $collection = self::select($selectCollection)->whereIn('status',
            [self::STATUS_DRAFT_EDIT, self::STATUS_FEEDBACK_EDIT, self::STATUS_SUBMIITED_EDIT])
            ->where('project_id', $projectId)
            ->get();
        if (count($collection)) {
            foreach ($collection as $item) {
                $itemParent = self::select($selectCollection)
                    ->whereIn('status', [self::STATUS_APPROVED])
                    ->where('project_id', $projectId)
                    ->where('id', $item->parent_id)
                    ->first();
                if ($itemParent) {
                    $result[TaskWoChange::FLAG_STAGE][TaskWoChange::FLAG_STATUS_EDIT][] = [
                        TaskWoChange::FLAG_STATUS_EDIT_OLD => 
                            self::toArrayLabelStageItem($itemParent, $stagesLabel),
                        TaskWoChange::FLAG_STATUS_EDIT_NEW => 
                            self::toArrayLabelStageItem($item, $stagesLabel),
                    ];
                }
            }
        }
        $result[TaskWoChange::FLAG_STAGE][TaskWoChange::FLAG_TYPE_TEXT] 
            = TaskWoChange::FLAG_TYPE_MULTI;
        return $result;
    }
    
    /**
     * to array with stage label
     * 
     * @param object $collection
     * @return array
     */
    protected static function toArrayLabelStage($collection, array $stagesLabel = [])
    {
        if (!count($collection)) {
            return [];
        }
        if (!$stagesLabel) {
            $stagesLabel = self::getAllStage();
        }
        $result = [];
        $arrayColumn = [
            'id',
            'stage',
            'description',
            'milestone',
            'qua_gate_plan',
            'parent_id'
        ];
        $i = 0;
        foreach ($collection as $item) {
            foreach ($arrayColumn as $column) {
                if ($column == 'stage') {
                    $result[$i][$column] = 
                        self::getStageTitle($item->{$column}, $stagesLabel);
                } else {
                    $result[$i][$column] = $item->{$column};
                }
            }
            $i++;
        }
        return $result;
    }
    
    /**
     * to array with stage label
     * 
     * @param object $collection
     * @return array
     */
    protected static function toArrayLabelStageItem($item, array $stagesLabel = [])
    {
        if (!$item) {
            return [];
        }
        if (!$stagesLabel) {
            $stagesLabel = StageAndMilestone::getAllStage();
        }
        $result = [];
        $arrayColumn = [
            'id',
            'stage',
            'description',
            'milestone',
            'qua_gate_plan',
            'parent_id'
        ];
        foreach ($arrayColumn as $column) {
            if ($column == 'stage') {
                $result[$column] = 
                    StageAndMilestone::getStageTitle($item->{$column}, $stagesLabel);
            } else {
                $result[$column] = $item->{$column};
            }
        }
        return $result;
    }
    
    /**
     * get column name to compare changes
     * 
     * @return array
     */
    public static function getColumnChanges()
    {
        return [
            'stage' => 'Title',
            'description' => 'Description',
            'milestone' => 'Milestone',
            'qua_gate_plan' => 'Quality gate plan date',
        ];
    }
    
    /**
     * update time committed_date for delivers of project
     * @param model $project
     * @param model $projectDraf
     */
    public static function updateTime($project, $projectDraf) {
        $projStart = $project->start_at;
        $projEnd = $project->end_at;
        if($projectDraf) {
            $projStart = $projectDraf->start_at;
            $projEnd = $projectDraf->end_at;
        }
        $tableStages = self::getTableName();
        $stages = self::whereNotIn('id', function($query) use ($tableStages, $project){
                    $query->select('parent_id')->from($tableStages)
                            ->where('project_id', $project->id)
                            ->whereNotNull('parent_id')
                            ->whereNull('deleted_at');
                })
                ->where('project_id', $project->id)
                ->get();
        foreach ($stages as $stage) {
            $change = false;
            if($stage->parent_id || 
                in_array($stage->status, [
                    self::STATUS_DRAFT, 
                    self::STATUS_DRAFT_EDIT,
                    self::STATUS_SUBMITTED,
                    self::STATUS_SUBMIITED_EDIT,
                    self::STATUS_FEEDBACK,
                    self::STATUS_FEEDBACK_EDIT
                ])
            ) {
                if ($stage->qua_gate_plan > $projEnd) {
                    $change = true;
                    $stage->qua_gate_plan = $projEnd;
                } elseif ($stage->qua_gate_plan < $projStart) {
                    $change = true;
                    $stage->qua_gate_plan = $projStart;
                }
                if ($change) {
                    $stage->save();
                }
            } else {
                if ($stage->status == self::STATUS_APPROVED) {
                    $stageDraf = new self();
                    $stageDraf = $stage->replicate();
                    if ($stageDraf->qua_gate_plan > $projEnd) {
                        $change = true;
                        $stageDraf->qua_gate_plan = $projEnd;
                    } elseif ($stageDraf->qua_gate_plan < $projStart) {
                        $change = true;
                        $stageDraf->qua_gate_plan = $projStart;
                    }
                    if ($change) {
                        $stageDraf->status = self::STATUS_DRAFT_EDIT;
                        $stageDraf->parent_id = $stage->id;
                        $stageDraf->save();
                    }
                }
                
            }
        }
    }

    /**
     * @param int $oldProjectId
     * @param int $newProjectId
     * @return bool|null
     */
    public static function cloneProjectStage($oldProjectId, $newProjectId)
    {
        $stages = self::where('project_id', $oldProjectId)
            ->where('status', self::STATUS_APPROVED)
            ->whereNull('deleted_at')
            ->get();

        foreach ($stages as $item) {
            $stageId = $item->id;
            unset($item->id);
            unset($item->created_at);
            unset($item->updated_at);
            $item->setData([
                'project_id' => $newProjectId,
                'created_by' => auth()->id(),
                'task_id' => null,
            ]);
            $cloneStageId = self::insertGetId($item->toArray());
            ProjDeliverable::cloneProjectDeliverable($oldProjectId, $newProjectId, $stageId, $cloneStageId);
        }
        return null;
    }
}
