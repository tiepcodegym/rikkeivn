<?php

namespace Rikkei\Project\Model;

use Carbon\Carbon;
use Rikkei\Team\View\Permission;
use Illuminate\Database\Eloquent\SoftDeletes;
use Rikkei\Project\Model\Task;
use Rikkei\Core\View\CacheHelper;
use Illuminate\Support\Facades\DB;
use Lang;
use Rikkei\Project\View\View;

class ProjQuality extends ProjectWOBase
{    
    use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'proj_qualities';
    
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
    protected $fillable = ['project_id', 'billable_effort', 'plan_effort', 'cost_approved_production', 'status', 'approved_cost'];

    /**
     * get quality 
     * 
     * @param int $projectId
     * @return type
     */
    public static function getFollowProject($projectId)
    {
        if ($item = CacheHelper::get(self::KEY_CACHE_WO, $projectId)) {
            return $item;
        }
        $item = self::select('billable_effort', 'plan_effort', 'cost_approved_production')
            ->where('project_id', $projectId)
            ->where('status', self::STATUS_APPROVED)
            ->first();
        CacheHelper::put(self::KEY_CACHE_WO, $item, $projectId);
        return $item;
    }

    /*
     * add quality
     * @param array
     */
    public static function insertQuality($input)
    {
        DB::beginTransaction();
        try {
            $arrayLablelStatus = self::getLabelStatusForProjectLog();
            $labelElement = self::getLabelElementForWorkorder()[Task::TYPE_WO_QUALITY];
            if(isset($input['isAddNew'])) {
                $quality = new ProjQuality();
                $quality->status = self::STATUS_DRAFT;
                $quality->project_id = $input['project_id'];
                $quality->created_by = Permission::getInstance()->getEmployee()->id;
                $status = self::STATUS_DRAFT;
            } else {
                $quality = self::find($input['id']);
                if ($quality->status == self::STATUS_FEEDBACK_EDIT) {
                    $status = self::STATUS_FEEDBACK_EDIT;
                    $quality->status = self::STATUS_DRAFT_EDIT;
                }
                if ($quality->status == self::STATUS_FEEDBACK) {
                    $status = self::STATUS_FEEDBACK;
                    $quality->status = self::STATUS_DRAFT;
                }
                if ($quality->status == self::STATUS_DRAFT_EDIT) {
                    $status = self::STATUS_DRAFT_EDIT;
                }
                if ($quality->status == self::STATUS_DRAFT) {
                    $status = self::STATUS_UPDATED_DRAFT;
                }
            }
            $quality->fill($input['data']);
            $qualityAttributes = $quality->attributes;
            $qualityOrigin = $quality->original;
            $quality->save();
            $statusText = $arrayLablelStatus[$status];
            if ($status == self::STATUS_DRAFT || $status == self::STATUS_EDIT_APPROVED) {
                View::insertProjectLogWO($input['project_id'], $statusText, $labelElement, null, null);
            } else {
                View::insertProjectLogWO($input['project_id'], $statusText, $labelElement, $qualityAttributes, $qualityOrigin);
            }
            DB::commit();
            CacheHelper::forget(self::KEY_CACHE_WO, $input['project_id']);
            return true;
        } catch (Exception $ex) {
            DB::rollback();
            return false;
        }
    }                

    /**
     * get quality draft
     * @param int
     * @param boolean
     * @return object
     */
    public static function getQualityDraftOld($id, $type = null)
    {
        $item = self::select('id', 'billable_effort', 'plan_effort', 'status')
            ->where('project_id', $id)
            ->where('status', '!=', self::STATUS_APPROVED);
        if ($type) {
            $item = $item->whereNull('billable_effort');
        } else {
            $item = $item->whereNull('plan_effort');
        }
        return $item->first();
    }

    public static function getQualityDraft($id, $field)
    {
        $item = self::select('id', 'billable_effort', 'plan_effort', 'cost_approved_production', 'status')
            ->where('project_id', $id)
            ->where('status', '!=', self::STATUS_APPROVED);
        switch ($field) {
            case 'billable_effort': //billable
                $item->whereNull('plan_effort')
                    ->whereNull('cost_approved_production');
                break;
            case 'plan_effort': //plan
                $item->whereNull('billable_effort')
                    ->whereNull('cost_approved_production');
                break;
            case 'cost_approved_production': //production cost
                $item->whereNull('billable_effort')
                    ->whereNull('plan_effort');
                break;
            default:
                return null;
        }
        return $item->first();
    }

    /*
     * delete quality
     * @param array
     * @return boolean
     */
    public static function deleteQuality($input)
    {
        $arrayLablelStatus = self::getLabelStatusForProjectLog();
        $labelElement = self::getLabelElementForWorkorder()[Task::TYPE_WO_QUALITY];
        $quality = self::find($input['id']);
        if ($quality) {
            if($quality->status == self::STATUS_APPROVED) {
                $qualityDelete = $quality->replicate();
                $qualityDelete->status = self::STATUS_DRAFT_DELETE;
                $qualityDelete->parent_id = $input['id'];
                $status = self::STATUS_DELETE_APPROVED;
                if ($qualityDelete->save()) {
                    CacheHelper::forget(self::KEY_CACHE_WO, $input['project_id']);
                    $statusText = $arrayLablelStatus[$status];
                    View::insertProjectLogWO($input['project_id'], $statusText, $labelElement, null, null);
                    return true;
                }
            } else {
                if ($quality->status == self::STATUS_DRAFT_EDIT) {
                    $status = self::STATUS_DELETE_DRAFT_EDIT;
                } else if ($quality->status == self::STATUS_DRAFT) {
                    $status = self::STATUS_DELETE_DRAFT;
                } else if ($quality->status == self::STATUS_FEEDBACK_DELETE) {
                    $status = self::STATUS_FEEDBACK_DELETE;
                } else if ($quality->status == self::STATUS_FEEDBACK_EDIT) {
                    $status = self::STATUS_DELETE_FEEDBACK_EDIT;
                }  else if ($quality->status == self::STATUS_FEEDBACK) {
                    $status = self::STATUS_DELETE_FEEDBACK;
                }  else if ($quality->status == self::STATUS_DRAFT_DELETE) {
                    $status = self::STATUS_DRAFT_DELETE;
                }
                if ($quality->delete()) {
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
        $qualityDraft = self::where('project_id', $input['project_id']);
        $quality = self::where('project_id', $input['project_id'])->where('status', self::STATUS_APPROVED)->first();
        if ($typeSubmit) {
            $qualityDraft = $qualityDraft->whereIn('status', [
                self::STATUS_DRAFT, 
                self::STATUS_FEEDBACK
            ]);
        } else {
            $qualityDraft = $qualityDraft->where('status', self::STATUS_DRAFT);
        }
        $qualityDraft = $qualityDraft->get();
        if(count($qualityDraft) > 0) {
            $title = Lang::get('project::view.Change effort for Quality');
            $content .= view('project::template.content-task', [
                'inputs' => $qualityDraft, 
                'title' => null, 
                'type' => $typeWO ,
                'quality' => $quality]
            )->render();
        }
        return $content;
    }

    /**
     * update status when submit workorder
     * @param array
     */
    public static function updateStatusWhenSubmitWorkorder($task, $input)
    {
        $qualityDraft = self::where('project_id', $input['project_id'])
                                    ->whereIn('status', [self::STATUS_DRAFT, self::STATUS_FEEDBACK])
                                    ->get();
        if(count($qualityDraft) > 0) {
            foreach($qualityDraft as $quality) {
                $quality->status = self::STATUS_SUBMITTED;
                $quality->task_id = $task->id;
                $quality->save();
            }
        }

        $qualityEdit = self::where('project_id', $input['project_id'])
                                        ->whereIn('status', [self::STATUS_DRAFT_EDIT, self::STATUS_FEEDBACK_EDIT])
                                        ->whereNotNull('parent_id')
                                        ->get();
        if(count($qualityEdit) > 0) {
            foreach($qualityEdit as $quality) {
                $quality->status = self::STATUS_SUBMIITED_EDIT;
                $quality->task_id = $task->id;
                $quality->save();
            }
        }

        $qualityDelete = self::where('project_id', $input['project_id'])
                                    ->whereIn('status', [self::STATUS_DRAFT_DELETE, self::STATUS_FEEDBACK_DELETE])
                                    ->get();
        if(count($qualityDelete)) {
            foreach($qualityDelete as $quality) {
                $quality->status = self::STATUS_SUBMMITED_DELETE;
                $quality->task_id = $task->id;
                $quality->save();
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
        $qualityDraft = self::where('project_id', $projectId);
        if ($statusTask == Task::STATUS_APPROVED) {
            if ($type == self::TYPE_ADD) {
                $qualityDraft = $qualityDraft->where('status', self::STATUS_REVIEWED);
            } else if($type == self::TYPE_EDIT) {
                $qualityDraft = $qualityDraft->where('status', self::STATUS_REVIEWED_EDIT);
            } else {
                $qualityDraft = $qualityDraft->where('status', self::STATUS_REVIEWED_DELETE);
            }
        } else if ($statusTask == Task::STATUS_FEEDBACK || $statusTask == Task::STATUS_REVIEWED) {
            if ($type == self::TYPE_ADD) {
                $qualityDraft = $qualityDraft->whereIn('status', [self::STATUS_SUBMITTED, self::STATUS_REVIEWED]);
            } else if($type == self::TYPE_EDIT) {
                $qualityDraft = $qualityDraft->whereIn('status', [self::STATUS_SUBMIITED_EDIT, self::STATUS_REVIEWED_EDIT]);
            } else {
                $qualityDraft = $qualityDraft->whereIn('status', [self::STATUS_SUBMMITED_DELETE, self::STATUS_REVIEWED_DELETE]);
            }
        }
        $qualityDraft = $qualityDraft->get();
        if(count($qualityDraft) > 0) {
            if ($statusTask == Task::STATUS_APPROVED) {
                foreach($qualityDraft as $quality) {
                    if ($quality->billable_effort) {
                        $billableEffort = $quality->billable_effort;
                    }
                    if ($quality->plan_effort) {
                        $planEffort = $quality->plan_effort;
                    }
                    if ($quality->cost_approved_production) {
                        $approvedProdCost = $quality->cost_approved_production;
                    }
                    $qualityParent = self::find($quality->parent_id);
                    if($qualityParent) {
                        $qualityParent->delete();
                    }
                    $quality->delete();
                }
                $qualityApproved = self::where('project_id', $projectId)
                                        ->where('status', self::STATUS_APPROVED)
                                        ->first();
                if (!$qualityApproved) {
                    $qualityApproved = new ProjQuality;
                    $qualityApproved->project_id = $projectId;
                    $qualityApproved->status = self::STATUS_APPROVED;
                }
                if(isset($billableEffort)) {
                    $qualityApproved->billable_effort = $billableEffort;
                }
                if(isset($planEffort)) {
                    $qualityApproved->plan_effort = $planEffort;
                }
                if (isset($approvedProdCost)) {
                    $qualityApproved->cost_approved_production = $approvedProdCost;
                }
                $qualityApproved->save();
            } else if ($statusTask == Task::STATUS_REVIEWED) {
                foreach($qualityDraft as $quality) {
                    if ($quality->status == self::STATUS_SUBMITTED) {
                        $quality->status = self::STATUS_REVIEWED;
                    }
                    if ($quality->status == self::STATUS_SUBMIITED_EDIT) {
                        $quality->status = self::STATUS_REVIEWED_EDIT;
                    }
                    if ($quality->status == self::STATUS_SUBMMITED_DELETE) {
                        $quality->status = self::STATUS_REVIEWED_DELETE;
                    }
                    $quality->save();
                }
            } else if ($statusTask == Task::STATUS_FEEDBACK) {
                foreach($qualityDraft as $quality) {
                    if ($quality->status == self::STATUS_SUBMITTED ||
                        $quality->status == self::STATUS_REVIEWED) {
                        $quality->status = self::STATUS_FEEDBACK;
                    }
                    if ($quality->status == self::STATUS_SUBMIITED_EDIT ||
                        $quality->status == self::STATUS_REVIEWED_EDIT) {
                        $quality->status = self::STATUS_FEEDBACK_EDIT;
                    }
                    if ($quality->status == self::STATUS_SUBMMITED_DELETE ||
                        $quality->status == self::STATUS_REVIEWED_DELETE) {
                        $quality->status = self::STATUS_FEEDBACK_DELETE;
                    }
                    $quality->save();
                }
            }
        }
    }

    /**
     * check status submit
     * @param int
     * @return boolean
     */
    public static function checkStatusSubmit($projectId)
    {
        $item = self::where('project_id', $projectId)
            ->whereIn('status', 
                [self::STATUS_DRAFT, self::STATUS_DRAFT_EDIT, 
                self::STATUS_DRAFT_DELETE, self::STATUS_FEEDBACK])
            ->select(DB::raw('COUNT(*) as count'))
            ->first();
        if ($item && $item->count) {
            return true;
        }
        return false;
    }

    /**
     * get conten table after submit
     * @param array
     * @return string
     */
    public static function getContentTable($project)
    {
        $permission = View::checkPermissionEditWorkorder($project);
        $permissionUpdateNote = $permission['permissionEidt'];
        $projectNote = ProjectWONote::getProjectWoNote($project->id);

        $projectPoint = ProjectPoint::findFromProject($project->id);
        $projectPointInformation = View::getProjectPointInfo(
                $project, 
                $projectPoint
            );
        $permissionEdit = $permission['persissionEditPM'] || $permission['permissionEditSubPM'] || $permission['permissionEditPqa'];
        $checkEditWorkOrder = Task::checkEditWorkOrder($project->id);
        $qualityPlans = Task::getListTaskQuality($project->id);
        $qualityPlan = QualityPlan::getQualityPlanOfProject($project->id);
        return view('project::components.quality', [
            'permissionUpdateNote' => $permissionUpdateNote,
            'projectNote' => $projectNote,
            'projectPointInformation' => $projectPointInformation, 'detail' => true,
            'project' => $project,
            'permissionEdit' => $permissionEdit, 
            'checkEditWorkOrder' => $checkEditWorkOrder, 
            'qualityPlans' => $qualityPlans, 
            'qualityPlan' => $qualityPlan, 
            ])->render();
    }

    /**
     * edit basic info
     * @param array
     * @param array
     * @return array
     */
    public static function editBasicInfo($data, $project)
    {
        $quality = self::getFollowProject($project->id);
        $qualityDraft = self::getQualityDraft($project->id, $data['name']);
        if (!$qualityDraft) {
            $qualityDraft = new ProjQuality();
            $qualityDraft->status = self::STATUS_DRAFT;
            $qualityDraft->project_id = $project->id;
            $qualityDraft->created_by = Permission::getInstance()->getEmployee()->id;
        }
        $qualityDraft->{$data['name']} = $data['value'];
        $result = array();
        $result['status'] = false;
        if($qualityDraft->save()) {
            $result['status'] = true;
            $isChange = false;
            if (!$quality) {
                $isChange = true;
            } else {
                if($data['value'] != $quality->{$data['name']}) {
                    $isChange = true;
                } else {
                    $qualityDraft->delete();
                }
            }
            $result['isChange'] = $isChange;
        }
        CacheHelper::forget(self::KEY_CACHE_WO, $project->id);
        return $result;
    }

    /**
     * Get clone data and insert with new project ID
     * @param int $cloneId
     * @param int $newProjectId
     */
    public static function insertCloneEffort($cloneId, $newProjectId)
    {
        $item = self::select(
            'billable_effort',
            'plan_effort',
            'cost_approved_production',
            'approved_cost',
            'status',
            'parent_id'
        )
            ->where('project_id', $cloneId)
            ->where('status', self::STATUS_APPROVED)
            ->whereNull('deleted_at')
            ->first();
        if ($item) {
            $item->setData([
                'project_id' => $newProjectId,
                'created_by' => auth()->id(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
            self::insert($item->toArray());
        }
    }
}
