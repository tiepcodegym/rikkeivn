<?php

namespace Rikkei\Project\Model;

use Rikkei\Core\View\CacheHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\SoftDeletes;
use Rikkei\Team\View\Permission;
use Lang;
use Carbon\Carbon;
use Rikkei\Project\View\View;
use Rikkei\Team\View\Config;

class ProjDeliverable extends ProjectWOBase
{    
    use SoftDeletes;
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'proj_deliverables';
    
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
    protected $fillable = ['project_id', 'title', 'committed_date', 're_commited_date',
                            'actual_date', 'stage', 'note', 'change_request_by',
                            'status', 'type', 'state',
                            'start_at', 'type'];

    /**
     *  define changes request list  
     */
    const CHANGE_BY_CUSTOMER = 1;
    const CHANGE_BY_RIKKEI = 2;

    public static function getChangeList()
    {
        return [
            self::CHANGE_BY_CUSTOMER => Lang::get('project::view.Customer'),
            self::CHANGE_BY_RIKKEI => Lang::get('project::view.Rikkei'),
        ];
    }
    /**
     * Get the project deliverable child
     */
    public function projectDeliverableChild() {
        return $this->hasOne('Rikkei\Project\Model\ProjDeliverable', 'parent_id');
    }
    
    /**
     * get deliver aprroved
     * 
     * @param int $projectId
     * @return object
     */
    public static function getApprovedItem($projectId)
    {
        $pager = Config::getPagerDataQuery();
        $collection = self::select('id', 'title', 'committed_date', 're_commited_date', 'change_request_by', 'actual_date', 'stage')
            ->where('project_id', $projectId)
            ->where('status', self::STATUS_APPROVED)
            ->orderBy('committed_date', 'desc');
        self::pagerCollection($collection, $pager['limit'], $pager['page']);
        return $collection;
    }
    
    /**
     * get deliver aprroved
     * 
     * @param int $projectId
     * @return object
     */
    public static function getLastActualApprovedItem($projectId)
    {
        if ($item = CacheHelper::get(self::KEY_CACHE_WO, $projectId)) {
            return $item;
        }
        $item = self::select('actual_date')
            ->where('project_id', $projectId)
            ->where('status', self::STATUS_APPROVED)
            ->orderBy('committed_date', 'desc')
            ->take(1)
            ->first();
        if ($item && $item->actual_date) {
            $item = $item->actual_date;
        } else {
            return null;
        }
        CacheHelper::put(self::KEY_CACHE_WO, $item, $projectId);
        return $item;
    }
    
    /**
     * calulate deliver value
     * 
     * @param object $delivers
     * @param int $projectId
     * @return array
     */
    public static function getDeliverInfo($projectId)
    {
        if ($item = CacheHelper::get(self::KEY_CACHE_WO, $projectId)) {
            return $item;
        }
        $committedDateColumn = "IF(change_request_by = " . self::CHANGE_BY_CUSTOMER . ", `re_commited_date`, `committed_date`)";
        $item = self::select(DB::raw("SUM(case when date(`re_commited_date`) >= date(`actual_date`) AND change_request_by = " . self::CHANGE_BY_CUSTOMER . ' then 1 when date(`committed_date`) >= date(`actual_date`) then 1 else 0 end) as count_ontime, '
                . 'count(*) as count'))
            ->where('project_id', $projectId)
            ->where('status', self::STATUS_APPROVED)
            ->where(function ($query) use ($committedDateColumn) {
                $query->orWhereNotNull('actual_date')
                    ->orWhere(function ($query) use ($committedDateColumn) {
                        $now = Carbon::now();
                        $query->whereNull('actual_date')
                            ->whereDate(DB::raw($committedDateColumn), '<', $now->format('Y-m-d'));
                    });

            })
            ->first();
        $result = [
            'ontime' => null,
            'delay' => null,
            'total' => null
        ];
        if (!$item) {
            return $result;
        }
        $result['total'] = $item->count;
        $result['ontime'] = (int) $item->count_ontime;
        $result['delay'] = $item->count - $item->count_ontime;
        CacheHelper::put(self::KEY_CACHE_WO, $result, $projectId);
        return $result;
    }

    /*
     * get all deliverable by project id
     * @param int
     * @return collection
     */
    public static function getAllDeliverable($projectId)
    {
        $collection = self::select(['id', 'title', 'committed_date', 're_commited_date', 'actual_date', 'stage', 'note', 'status', 'stage_id', 'parent_id', 'change_request_by'])
                            ->where('project_id', $projectId)
                            ->orderBy('committed_date', 'desc')
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
     * add deliverable
     * @param array
     */
    public static function insertDeliverable($input)
    {
        DB::beginTransaction();
        try {
            $arrayLablelStatus = self::getLabelStatusForProjectLog();
            $labelElement = self::getLabelElementForWorkorder()[Task::TYPE_WO_DELIVERABLE];
            if (config('project.workorder_approved.deliverable')) {
                if (isset($input['isAddNew'])) {
                    $deliverable = new ProjDeliverable();
                    $deliverable->project_id = $input['project_id'];
                    $deliverable->status = self::STATUS_DRAFT;
                    $deliverable->committed_date = $input['committed_date_1'];
                    $deliverable->re_commited_date = !empty($input['re_commited_date_1']) ? $input['re_commited_date_1'] : null;
                    $deliverable->change_request_by = $input['change_request_by_1'];
                    $deliverable->title = $input['title_1'];
                    if ($input['stage_1']) {
                        $deliverable->stage_id = $input['stage_1'];
                    } else {
                        $deliverable->stage_id = null;
                    }
                    if (isset($input['actual_date_1']) && $input['actual_date_1']) {
                        $deliverable->actual_date = $input['actual_date_1'];
                    }
                    $deliverable->note = $input['note_1'];
                    $deliverable->created_by = Permission::getInstance()->getEmployee()->id;
                    $status = self::STATUS_DRAFT;
                    $deliverable->save();
                } else if (isset($input['isEdit'])) {
                    if (isset($input['status']) && $input['status'] == self::STATUS_APPROVED) {
                        $deliverable = self::where('parent_id', $input['id'])->first();
                        $delive = self::find($input['id']);
                        if ($deliverable) {
                            $deliverableOrigin = $deliverable->original;
                            $deliverable->committed_date = $input['committed_date_1'];
                            $deliverable->re_commited_date = !empty($input['re_commited_date_1']) ? $input['re_commited_date_1'] : null;
                            $deliverable->change_request_by = $input['change_request_by_1'];
                            $deliverable->title = $input['title_1'];
                            if ($input['stage_1']) {
                                $deliverable->stage_id = $input['stage_1'];
                            } else {
                                $deliverable->stage_id = null;
                            }
                            if (isset($input['actual_date_1']) && $input['actual_date_1']) {
                                $deliverable->actual_date = $input['actual_date_1'];
                            }
                            $deliverable->note = $input['note_1'];
                            $deliverableAttributes= $deliverable->attributes;

                            $status = self::STATUS_EDIT_APPROVED;
                            $isChangeValue = View::isChangeValue($delive, $deliverable);
                            if(!$isChangeValue) {
                                if ($deliverable->status == self::STATUS_DRAFT_EDIT) {
                                    $deliverable->forceDelete();
                                } else {
                                    $deliverable->status = self::STATUS_FEEDBACK_DELETE;
                                    $deliverable->save();
                                }
                                DB::commit();
                                CacheHelper::forget(self::KEY_CACHE_WO, $input['project_id']);
                                return true;
                            }
                        } else {
                            $deliverable = new ProjDeliverable();
                            $deliverable->committed_date = $input['committed_date_1'];
                            $deliverable->re_commited_date = !empty($input['re_commited_date_1']) ? $input['re_commited_date_1'] : null;
                            $deliverable->change_request_by = $input['change_request_by_1'];
                            $deliverable->title = $input['title_1'];
                            if ($input['stage_1']) {
                                $deliverable->stage_id = $input['stage_1'];
                            } else {
                                $deliverable->stage_id = null;
                            }
                            if (isset($input['actual_date_1'])) {
                                $deliverable->actual_date = $input['actual_date_1'] ? $input['actual_date_1'] : null;
                            }
                            $deliverable->note = $input['note_1'];

                            $isChangeValue = View::isChangeValue($delive, $deliverable);
                            $deliverableOrigin = $deliverable->original;
                            $deliverableAttributes= $deliverable->attributes;
                            if($isChangeValue) {
                                $status = self::STATUS_EDIT_APPROVED;
                                $deliverable->project_id = $input['project_id'];
                                $deliverable->status = self::STATUS_DRAFT_EDIT;
                                $deliverable->parent_id = $input['id'];
                                $deliverable->created_by = Permission::getInstance()->getEmployee()->id;
                                $deliverableParent = self::find($input['id']);
                                $deliverable->stage = $deliverableParent->stage;
                            } else {
                                return true;
                            }
                        }
                    } else {
                        $deliverable = self::find($input['id']);
                        switch ($deliverable->status) {
                            case self::STATUS_FEEDBACK_EDIT:
                                $status = self::STATUS_FEEDBACK_EDIT;
                                $deliverable->status = self::STATUS_DRAFT_EDIT;
                                break;
                            case self::STATUS_FEEDBACK:
                                $status = self::STATUS_FEEDBACK;
                                $deliverable->status = self::STATUS_DRAFT;
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
                        if ($deliverable->parent_id) {
                            $delive = self::find($deliverable->parent_id);
                        } else {
                            $isChange = false;
                        }

                        $deliverable->committed_date = $input['committed_date_1'];
                        $deliverable->re_commited_date = !empty($input['re_commited_date_1']) ? $input['re_commited_date_1'] : null;
                        $deliverable->change_request_by = $input['change_request_by_1'];
                        $deliverable->title = $input['title_1'];
                        if ($input['stage_1']) {
                            $deliverable->stage_id = $input['stage_1'];
                        } else {
                            $deliverable->stage_id = null;
                        }
                        if (isset($input['actual_date_1'])) {
                            $deliverable->actual_date = $input['actual_date_1'] ? $input['actual_date_1'] : null;
                        }
                        $deliverable->note = $input['note_1'];
                        $deliverableAttributes = $deliverable->attributes;
                        $deliverableOrigin = $deliverable->original;
                    }
                    if(!isset($isChange)) {
                        if (!View::isChangeValue($delive, $deliverable)) {
                            if($deliverable->status == self::STATUS_DRAFT_EDIT && isset($deliverable->id)) {
                                $deliverable->forceDelete();
                                DB::commit();
                                CacheHelper::forget(self::KEY_CACHE_WO, $input['project_id']);
                                return true;
                            }
                        }
                    }
                    $deliverable->save();
                }
                $statusText = $arrayLablelStatus[$status];
                if ($status == self::STATUS_DRAFT || $status == self::STATUS_EDIT_APPROVED) {
                    View::insertProjectLogWO($input['project_id'], $statusText, $labelElement, null, null);
                } else {
                    View::insertProjectLogWO($input['project_id'], $statusText, $labelElement, $deliverableAttributes, $deliverableOrigin);
                }
            } else {
                if (isset($input['id'])) {
                    $status = self::STATUS_EDIT;
                    $deliverable = self::find($input['id']);
                } else {
                    $status = self::STATUS_ADD;
                    $deliverable = new ProjDeliverable;
                    $deliverable->project_id = $input['project_id'];
                    $deliverable->created_by = Permission::getInstance()->getEmployee()->id;
                    $deliverable->status = self::STATUS_APPROVED;
                }
                $deliverable->committed_date = $input['committed_date_1'];
                $deliverable->re_commited_date = !empty($input['re_commited_date_1']) ? $input['re_commited_date_1'] : null;
                $deliverable->change_request_by = $input['change_request_by_1'];
                $deliverable->title = $input['title_1'];
                if ($input['stage_1']) {
                    $deliverable->stage_id = $input['stage_1'];
                } else {
                    $deliverable->stage_id = null;
                }
                if (isset($input['actual_date_1']) && $input['actual_date_1']) {
                    $deliverable->actual_date = $input['actual_date_1'];
                }
                $deliverable->note = $input['note_1'];
                $deliverableAttributes = $deliverable->attributes;
                $deliverableOrigin = $deliverable->original;
                $deliverable->save();

                $statusText = $arrayLablelStatus[$status];
                if ($status == self::STATUS_ADD) {
                    View::insertProjectLogWO($input['project_id'], $statusText, $labelElement, null, null);
                } else {
                    View::insertProjectLogWO($input['project_id'], $statusText, $labelElement, $deliverableAttributes, $deliverableOrigin);
                }
            }
            DB::commit();
            CacheHelper::forget(self::KEY_CACHE_WO, $input['project_id']);
            return true;
        } catch (Exception $ex) {
            DB::rollback();
            return false;
        }
    }

    /*
     * delete deliverable 
     * @param array
     * @return boolean
     */
    public static function deleteDeliverable($input) {
        $arrayLablelStatus = self::getLabelStatusForProjectLog();
        $labelElement = self::getLabelElementForWorkorder()[Task::TYPE_WO_DELIVERABLE];
        if (config('project.workorder_approved.deliverable')) {
            $deliverable = self::find($input['id']);
            if ($deliverable) {
                if($deliverable->status == self::STATUS_APPROVED) {
                    $deliverableDelete = $deliverable->replicate();
                    $deliverableDelete->status = self::STATUS_DRAFT_DELETE;
                    $deliverableDelete->parent_id = $input['id'];
                    $status = self::STATUS_DELETE_APPROVED;
                    if ($deliverableDelete->save()) {
                        CacheHelper::forget(self::KEY_CACHE_WO, $input['project_id']);
                        $statusText = $arrayLablelStatus[$status];
                        View::insertProjectLogWO($input['project_id'], $statusText, $labelElement, null, null);
                        return true;
                    }
                } else {
                    switch ($deliverable->status) {
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
                    if ($deliverable->delete()) {
                        CacheHelper::forget(self::KEY_CACHE_WO, $input['project_id']);
                        $statusText = $arrayLablelStatus[$status];
                        View::insertProjectLogWO($input['project_id'], $statusText, $labelElement, null, null);
                        return true;
                    } 
                }
            }
        } else {
            $status = self::STATUS_DELETE;
            $deliverable = self::find($input['id']);
            if ($deliverable) {
                if ($deliverable->delete()) {
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
        $allStage = StageAndMilestone::getAllStage();
        $deliverableDraft = self::where('project_id', $input['project_id']);
        if ($typeSubmit) {
            $deliverableDraft = $deliverableDraft->whereIn('status', [self::STATUS_DRAFT, self::STATUS_FEEDBACK]);
        } else {
            $deliverableDraft = $deliverableDraft->where('status', self::STATUS_DRAFT);
        }
        $deliverableDraft = $deliverableDraft->get();
        if(count($deliverableDraft) > 0) {
            $title = Lang::get('project::view.Add object for Deliverable');
            $content .= view('project::template.content-task', ['inputs' => $deliverableDraft, 'title' => $title, 'type' => $typeWO, 'allStage' => $allStage])->render();
        }

        $deliverableDraftEdit = self::where('project_id', $input['project_id']);
        if ($typeSubmit) {
            $deliverableDraftEdit = $deliverableDraftEdit->whereIn('status', [self::STATUS_DRAFT_EDIT, self::STATUS_FEEDBACK_EDIT]);
        } else {
            $deliverableDraftEdit = $deliverableDraftEdit->where('status', self::STATUS_DRAFT_EDIT);
        }
        $deliverableDraftEdit = $deliverableDraftEdit->get();
        if(count($deliverableDraftEdit) > 0) {
            $title = Lang::get('project::view.Edit object for Deliverable');
            $content .= view('project::template.content-task', ['inputs' => $deliverableDraftEdit, 'title' => $title, 'type' => $typeWO, 'allStage' => $allStage])->render();
        }
        
        $deliverableDelete = self::where('project_id', $input['project_id']);
        if ($typeSubmit) {
            $deliverableDelete = $deliverableDelete->whereIn('status', [self::STATUS_DRAFT_DELETE, self::STATUS_FEEDBACK_DELETE]);
        } else {
            $deliverableDelete = $deliverableDelete->where('status', self::STATUS_DRAFT_DELETE);
        }
        $deliverableDelete = $deliverableDelete->get();
        if(count($deliverableDelete) > 0) {
            $title = Lang::get('project::view.Delete object for Deliverable');
            $content .= view('project::template.content-task', ['inputs' => $deliverableDelete, 'title' => $title, 'type' => $typeWO, 'allStage' => $allStage])->render();
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
        $deliverableDraft = self::where('project_id', $input['project_id'])
            ->whereIn('status', [self::STATUS_DRAFT, self::STATUS_FEEDBACK])
            ->get();
        if(count($deliverableDraft)) {
            foreach($deliverableDraft as $deliverable) {
                $deliverable->status = self::STATUS_SUBMITTED;
                $deliverable->task_id = $task->id;
                $deliverable->save();
            }
        }

        $deliverableEdit = self::where('project_id', $input['project_id'])
                                        ->whereIn('status', [self::STATUS_DRAFT_EDIT, self::STATUS_FEEDBACK_EDIT])
                                        ->whereNotNull('parent_id')
                                        ->get();
        if(count($deliverableEdit) > 0) {
            foreach($deliverableEdit as $deliverable) {
                $deliverable->status = self::STATUS_SUBMIITED_EDIT;
                $deliverable->task_id = $task->id;
                $deliverable->save();
            }
        }

        $deliverableDelete = self::where('project_id', $input['project_id'])
                                    ->whereIn('status', [self::STATUS_DRAFT_DELETE, self::STATUS_FEEDBACK_DELETE])
                                    ->get();
        if(count($deliverableDelete)) {
            foreach($deliverableDelete as $deliverable) {
                $deliverable->status = self::STATUS_SUBMMITED_DELETE;
                $deliverable->task_id = $task->id;
                $deliverable->save();
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
        $deliverableDraft = self::where('project_id', $projectId);
        if ($statusTask == Task::STATUS_APPROVED) {
            if ($type == self::TYPE_ADD) {
                $deliverableDraft = $deliverableDraft->where('status', self::STATUS_REVIEWED);
            } else if($type == self::TYPE_EDIT) {
                $deliverableDraft = $deliverableDraft->where('status', self::STATUS_REVIEWED_EDIT);
            } else {
                $deliverableDraft = $deliverableDraft->where('status', self::STATUS_REVIEWED_DELETE);
            }
        } else if ($statusTask == Task::STATUS_FEEDBACK || $statusTask == Task::STATUS_REVIEWED) {
            if ($type == self::TYPE_ADD) {
                $deliverableDraft = $deliverableDraft->whereIn('status', [self::STATUS_SUBMITTED, self::STATUS_REVIEWED]);
            } else if($type == self::TYPE_EDIT) {
                $deliverableDraft = $deliverableDraft->whereIn('status', [self::STATUS_SUBMIITED_EDIT, self::STATUS_REVIEWED_EDIT]);
            } else {
                $deliverableDraft = $deliverableDraft->whereIn('status', [self::STATUS_SUBMMITED_DELETE, self::STATUS_REVIEWED_DELETE]);
            }
        }
        $deliverableDraft = $deliverableDraft->get();
        if(count($deliverableDraft) > 0) {
            if ($statusTask == Task::STATUS_APPROVED) {
                if ($type == self::TYPE_DELETE) {
                    foreach($deliverableDraft as $deliverable) {
                        $deliverableParent = self::find($deliverable->parent_id);
                        $deliverable->delete();
                        if($deliverableParent) {
                            $deliverableParent->delete();
                        }
                    }
                } else if($type == self::TYPE_ADD) {
                    foreach($deliverableDraft as $deliverable) {
                        $deliverable->status = self::STATUS_APPROVED;
                        $deliverable->save();
                    }
                } else { // item edit
                    foreach($deliverableDraft as $deliverable) {
                        $deliverableParent = self::find($deliverable->parent_id);
                        if($deliverableParent) {
                            View::updateValueWhenApproved($deliverableParent, $deliverable);
                        }
                    }
                }
            } else if ($statusTask == Task::STATUS_REVIEWED) {
                foreach($deliverableDraft as $deliverable) {
                    if ($deliverable->status == self::STATUS_SUBMITTED) {
                        $deliverable->status = self::STATUS_REVIEWED;
                    }
                    if ($deliverable->status == self::STATUS_SUBMIITED_EDIT) {
                        $deliverable->status = self::STATUS_REVIEWED_EDIT;
                    }
                    if ($deliverable->status == self::STATUS_SUBMMITED_DELETE) {
                        $deliverable->status = self::STATUS_REVIEWED_DELETE;
                    }
                    $deliverable->save();
                }
            } else if ($statusTask == Task::STATUS_FEEDBACK) {
                foreach($deliverableDraft as $deliverable) {
                    if ($deliverable->status == self::STATUS_SUBMITTED ||
                        $deliverable->status == self::STATUS_REVIEWED) {
                        $deliverable->status = self::STATUS_FEEDBACK;
                    }
                    if ($deliverable->status == self::STATUS_SUBMIITED_EDIT ||
                        $deliverable->status == self::STATUS_REVIEWED_EDIT) {
                        $deliverable->status = self::STATUS_FEEDBACK_EDIT;
                    }
                    if ($deliverable->status == self::STATUS_SUBMMITED_DELETE ||
                        $deliverable->status == self::STATUS_REVIEWED_DELETE) {
                        $deliverable->status = self::STATUS_FEEDBACK_DELETE;
                    }
                    $deliverable->save();
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
        $allDeliverable = self::getAllDeliverable($project->id);
        $allStageOfProject = StageAndMilestone::getStageAndMilestoneOfProject($project->id)->get();
        $allStage = StageAndMilestone::getAllStage();
        return view('project::components.deliverable', ['permissionEdit' => $permissionEdit, 'checkEditWorkOrder' => $checkEditWorkOrder, 'allDeliverable' => $allDeliverable, 'detail' => true, 'allStageOfProject' => $allStageOfProject, 'allStage' => $allStage, 'project' => $project])->render();
    }

    /**
      * check has deliverable
      * @param int
      * @return int
      */
     public static function checkHasDeliverable($projectId)
     {
        return self::where('project_id', $projectId)->count();
     }
     
    /**
     * rewrite save model
     * 
     * @param array $options
     */
    public function save(array $options = array()) {
        try {
            $result = parent::save($options);
            CacheHelper::forget(self::KEY_CACHE_WO, $this->project_id);
            return $result;
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    /**
     * check error time where submit workorder
     * @param int
     * @param array
     * @return boolean
     */
    public static function checkErrorTime($projectId, $projectDraft)
    {
        $deliverables = self::where(function ($query) use ($projectDraft) {
                        $query->orWhereDate('committed_date', '<', $projectDraft->start_at)
                          ->orWhereDate('committed_date', '>', $projectDraft->end_at);
                    })
                    ->where('project_id', $projectId)
                    ->get();
        $checkError = false;
        foreach($deliverables as $deliverable) {
            if ($deliverable->projectDeliverableChild) {
                continue;
            }
            $checkError = true;
            break;
        }
        return $checkError;
    }

    /*
     * get deliverable current by project id
     * @param int
     * @return array
     */
    public static function getDeliverableCurrent($projectId)
    {
        return self::where('project_id', $projectId)
                    ->whereNotIn('status', [self::STATUS_DRAFT_DELETE, self::STATUS_SUBMMITED_DELETE, self::STATUS_REVIEWED_DELETE, self::STATUS_FEEDBACK_DELETE])->get();
    }
    
    /**
     * get delelivers by status
     * 
     * @param int
     * @param array
     * @return collection
     */
    public static function getDeliviersByStatus($status, $input)
    {
        $tableStage = StageAndMilestone::getTableName();
        $tableDeliver = self::getTableName();
        
        $collection = self::select([
                "{$tableDeliver}.id as id", 
                $tableDeliver . '.title',
                DB::raw("DATE(`{$tableDeliver}`.`committed_date`) as committed_date"),
                $tableStage . '.stage as stage_name',
                $tableDeliver . '.parent_id',
                $tableDeliver. '.re_commited_date',
                $tableDeliver. '.change_request_by',
                DB::raw("DATE(`{$tableDeliver}`.`actual_date`) as actual_date"),
            ])
            ->join($tableStage, "{$tableStage}.id", '=', "{$tableDeliver}.stage_id")
            ->whereIn($tableDeliver . '.status', $status)
            ->where($tableDeliver . '.project_id', $input['project_id']);
        if (isset($input['parent_id']) && $input['parent_id']) {
            $collection->where($tableDeliver . '.parent_id', $input['parent_id'])
                ->whereNotNull($tableDeliver . '.task_id');
        }
        if (isset($input['deliver_id']) && $input['deliver_id']) {
            $collection->where($tableDeliver . '.id', $input['deliver_id']);
        }
        if (StageAndMilestone::isUseSoftDelete()) {
            $collection->whereNull("{$tableStage}.deleted_at");
        }
        return $collection->get();
    }
    
    /**
     * rewrite changes object after submit
     * 
     * @param int $projectId
     * @param string $type
     */
    public static function getChangesAfterSubmit($projectId, $type = null) 
    {
        $result = [];
        $stagesLabel = StageAndMilestone::getAllStage();
        // add items
        $collection = self::getDeliviersByStatus(
            [self::STATUS_DRAFT, self::STATUS_FEEDBACK, self::STATUS_SUBMITTED],
            ['project_id' => $projectId]
        );
        if (count($collection)) {
            $result[TaskWoChange::FLAG_DELIVER][TaskWoChange::FLAG_STATUS_ADD] 
                = self::toArrayLabelStage($collection, $stagesLabel); 
        }
        
        // delete item
        $collection = self::getDeliviersByStatus(
            [self::STATUS_DRAFT_DELETE, self::STATUS_FEEDBACK_DELETE, self::STATUS_SUBMMITED_DELETE],
            ['project_id' => $projectId]
        );
        if (count($collection)) {
            $result[TaskWoChange::FLAG_DELIVER][TaskWoChange::FLAG_STATUS_DELETE] 
                = self::toArrayLabelStage($collection, $stagesLabel); 
        }
        
        // edit item
        $collection = self::getDeliviersByStatus(
            [self::STATUS_DRAFT_EDIT, self::STATUS_FEEDBACK_EDIT, self::STATUS_SUBMIITED_EDIT],
            ['project_id' => $projectId]
        );
        if (count($collection)) {
            foreach ($collection as $item) {
                $itemParent = self::getDeliviersByStatus(
                    [self::STATUS_APPROVED],
                    ['project_id' => $projectId, 'deliver_id' => $item->parent_id]
                )->first();
                if ($itemParent) {
                    $result[TaskWoChange::FLAG_DELIVER][TaskWoChange::FLAG_STATUS_EDIT][] = [
                        TaskWoChange::FLAG_STATUS_EDIT_OLD => self::toArrayLabelStageItem($itemParent, $stagesLabel),
                        TaskWoChange::FLAG_STATUS_EDIT_NEW => self::toArrayLabelStageItem($item, $stagesLabel),
                    ];
                }
            }
        }
        $result[TaskWoChange::FLAG_DELIVER][TaskWoChange::FLAG_TYPE_TEXT] 
            = TaskWoChange::FLAG_TYPE_MULTI;
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
            'title' => 'Title',
            'stage_name' => 'Stage',
            'committed_date' => 'Planned Release',
            're_commited_date' => 'Re-Plan Release',
            'actual_date' => 'Actual Release',
            'change_request_by' => 'Change request by',
        ];
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
            $stagesLabel = StageAndMilestone::getAllStage();
        }
        $result = [];
        $arrayColumn = [
            'id',
            'title',
            'committed_date',
            're_commited_date',
            'change_request_by',
            'actual_date',
            'stage_name',
            'parent_id'
        ];
        $i = 0;
        foreach ($collection as $item) {
            foreach ($arrayColumn as $column) {
                if ($column == 'stage_name') {
                    $result[$i][$column] = 
                        StageAndMilestone::getStageTitle($item->{$column}, $stagesLabel);
                } elseif ($column == 'change_request_by') {
                    $result[$i][$column] = !empty($item->{$column}) ? static::getChangeList()[$item->{$column}] : null;
                } else {
                    $result[$i][$column] = !empty($item->{$column}) ? $item->{$column} : null;
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
            'title',
            'committed_date',
            're_commited_date',
            'change_request_by',
            'actual_date',
            'stage_name',
            'parent_id'
        ];
        foreach ($arrayColumn as $column) {
            if ($column == 'stage_name') {
                $result[$column] = 
                    StageAndMilestone::getStageTitle($item->{$column}, $stagesLabel);
            } elseif ($column == 'change_request_by') {
                $result[$column] = !empty($item->{$column}) ? static::getChangeList()[$item->{$column}] : null;
            }
            else {
                $result[$column] = $item->{$column};
            }
        }
        return $result;
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
        $tableProjDeliver = self::getTableName();
        $delivers = self::whereNotIn('id', function($query) use ($tableProjDeliver, $project){
                    $query->select('parent_id')->from($tableProjDeliver)
                            ->where('project_id', $project->id)
                            ->whereNotNull('parent_id')
                            ->whereNull('deleted_at');
                })
                ->where('project_id', $project->id)
                ->get();
        foreach ($delivers as $deliver) {
            $change = false;
            if($deliver->parent_id || 
                in_array($deliver->status, [
                    self::STATUS_DRAFT, 
                    self::STATUS_DRAFT_EDIT,
                    self::STATUS_SUBMITTED,
                    self::STATUS_SUBMIITED_EDIT,
                    self::STATUS_FEEDBACK,
                    self::STATUS_FEEDBACK_EDIT
                ])
            ) {
                if ($deliver->committed_date > $projEnd) {
                    $change = true;
                    $deliver->committed_date = $projEnd;
                } elseif ($deliver->committed_date < $projStart) {
                    $change = true;
                    $deliver->committed_date = $projStart;
                }
                if ($change) {
                    $deliver->save();
                }
            } else {
                if ($deliver->status == self::STATUS_APPROVED) {
                    $deliverDraf = new self();
                    $deliverDraf = $deliver->replicate();
                    if ($deliverDraf->committed_date > $projEnd) {
                        $change = true;
                        $deliverDraf->committed_date = $projEnd;
                    } elseif ($deliverDraf->committed_date < $projStart) {
                        $change = true;
                        $deliverDraf->committed_date = $projStart;
                    }
                    if ($change) {
                        $deliverDraf->status = self::STATUS_DRAFT_EDIT;
                        $deliverDraf->parent_id = $deliver->id;
                        $deliverDraf->save();
                    }
                }
                
            }
        }
    }

    /**
     * @param int $oldProjectId
     * @param int $newProjectId
     * @param int $stageId
     * @param int $cloneStageId
     * @return bool|null
     */
    public static function cloneProjectDeliverable($oldProjectId, $newProjectId, $stageId, $cloneStageId)
    {
        $deliverable = self::where('project_id', $oldProjectId)
            ->where('stage_id', $stageId)
            ->where('status', self::STATUS_APPROVED)
            ->whereNull('deleted_at')
            ->get();

        foreach ($deliverable as $item) {
            unset($item->id);
            unset($item->created_at);
            unset($item->updated_at);
            unset($item->task_id);
            $item->setData([
                'project_id' => $newProjectId,
                'stage_id' => $cloneStageId,
                'created_by' => auth()->id(),
            ]);
            return self::insert($item->toArray());
        }
        return null;
    }
}
