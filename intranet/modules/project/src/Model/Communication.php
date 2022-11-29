<?php

namespace Rikkei\Project\Model;

use Rikkei\Team\View\Permission;
use Illuminate\Database\Eloquent\SoftDeletes;
use Rikkei\Project\Model\Task;
use Rikkei\Core\View\CacheHelper;
use DB;
use Lang;
use Rikkei\Project\View\View;

class Communication extends ProjectWOBase
{
    use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'proj_op_communications';

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
    protected $fillable = ['project_id', 'content',
                            'status', 'type', 'state',
                            'start_at', 'type'];

    /**
     * Get the communication child
     */
    public function projectCommunicationChild() {
        return $this->hasOne('Rikkei\Project\Model\Communication', 'parent_id');
    }

    /*
     * get all communication by project id
     * @param int
     * @return collection
     */
    public static function getAllCommunication($projectId)
    {
        if ($item = CacheHelper::get(self::KEY_CACHE_WO, $projectId)) {
            return $item;
        }
        $item = self::select(['id', 'content', 'status'])
            ->where('project_id', $projectId);
        if (config('project.workorder_approved.communication')) {
            $item = $item->whereNull('parent_id')
                        ->orderBy('updated_at', 'asc')
                        ->get();
        } else {
            $item = $item->orderBy('updated_at', 'asc')
                        ->get();
        }
        CacheHelper::put(self::KEY_CACHE_WO, $item, $projectId);
        return $item;
    }

    /*
     * add communication
     * @param array
     */
    public static function insertCommunication($input)
    {
        DB::beginTransaction();
        try {
            $arrayLablelStatus = self::getLabelStatusForProjectLog();
            $labelElement = self::getLabelElementForWorkorder()[Task::TYPE_WO_COMMINUCATION];
            if (config('project.workorder_approved.communication')) {
                if (isset($input['isAddNew'])) {
                    $communication = new Communication();
                    $communication->project_id = $input['project_id'];
                    $communication->status = self::STATUS_DRAFT;
                    $communication->content = $input['content_1'];
                    $communication->created_by = Permission::getInstance()->getEmployee()->id;
                    $status = self::STATUS_DRAFT;
                    $communication->save();
                } else if (isset($input['isEdit'])) {
                    if (isset($input['status']) && $input['status'] == self::STATUS_APPROVED) {
                        $status = self::STATUS_EDIT_APPROVED;
                        $communication = new Communication();
                        $communication->project_id = $input['project_id'];
                        $communication->status = self::STATUS_DRAFT_EDIT;
                        $communication->parent_id = $input['id'];
                        $communication->created_by = Permission::getInstance()->getEmployee()->id;
                    } else {
                        $communication = self::find($input['id']);
                        if ($communication->status == self::STATUS_FEEDBACK_EDIT) {
                            $status = self::STATUS_FEEDBACK_EDIT;
                            $communication->status = self::STATUS_DRAFT_EDIT;
                        }
                        if ($communication->status == self::STATUS_FEEDBACK) {
                            $status = self::STATUS_FEEDBACK;
                            $communication->status = self::STATUS_DRAFT;
                        }
                        if ($communication->status == self::STATUS_DRAFT_EDIT) {
                            $status = self::STATUS_DRAFT_EDIT;
                        }
                        if ($communication->status == self::STATUS_DRAFT) {
                            $status = self::STATUS_UPDATED_DRAFT;
                        }
                    }
                    $communication->content = $input['content_1'];
                    $communicationAttributes = $communication->attributes;
                    $communicationOrigin = $communication->original;
                    $communication->save();
                }
                $statusText = $arrayLablelStatus[$status];
                if ($status == self::STATUS_DRAFT || $status == self::STATUS_EDIT_APPROVED) {
                    View::insertProjectLogWO($input['project_id'], $statusText, $labelElement, null, null);
                } else {
                    View::insertProjectLogWO($input['project_id'], $statusText, $labelElement, $communicationAttributes, $communicationOrigin);
                }
            } else {
                if (isset($input['id'])) {
                    $status = self::STATUS_EDIT;
                    $communication = self::find($input['id']);
                } else {
                    $status = self::STATUS_ADD;
                    $communication = new Communication;
                    $communication->project_id = $input['project_id'];
                    $communication->created_by = Permission::getInstance()->getEmployee()->id;
                    $communication->status = self::STATUS_APPROVED;
                }
                $communication->content = $input['content_1'];
                $communicationAttributes = $communication->attributes;
                $communicationOrigin = $communication->original;
                $communication->save();

                $statusText = $arrayLablelStatus[$status];
                if ($status == self::STATUS_ADD) {
                    View::insertProjectLogWO($input['project_id'], $statusText, $labelElement, null, null);
                } else {
                    View::insertProjectLogWO($input['project_id'], $statusText, $labelElement, $communicationAttributes, $communicationOrigin);
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
     * delete communication
     * @param array
     * @return boolean
     */
    public static function deleteCommunication($input)
    {
        $arrayLablelStatus = self::getLabelStatusForProjectLog();
        $labelElement = self::getLabelElementForWorkorder()[Task::TYPE_WO_COMMINUCATION];
        if (config('project.workorder_approved.communication')) {
            $communication = self::find($input['id']);
            if ($communication) {
                if($communication->status == self::STATUS_APPROVED) {
                    $communicationDelete = $communication->replicate();
                    $communicationDelete->status = self::STATUS_DRAFT_DELETE;
                    $communicationDelete->parent_id = $input['id'];
                    $status = self::STATUS_DELETE_APPROVED;
                    if ($communicationDelete->save()) {
                        CacheHelper::forget(self::KEY_CACHE_WO, $input['project_id']);
                        return true;
                    }
                } else {
                    if ($communication->status == self::STATUS_DRAFT_EDIT) {
                        $status = self::STATUS_DELETE_DRAFT_EDIT;
                    } else if ($communication->status == self::STATUS_DRAFT) {
                        $status = self::STATUS_DELETE_DRAFT;
                    } else if ($communication->status == self::STATUS_FEEDBACK_DELETE) {
                        $status = self::STATUS_FEEDBACK_DELETE;
                    } else if ($communication->status == self::STATUS_FEEDBACK_EDIT) {
                        $status = self::STATUS_DELETE_FEEDBACK_EDIT;
                    }  else if ($communication->status == self::STATUS_FEEDBACK) {
                        $status = self::STATUS_DELETE_FEEDBACK;
                    }  else if ($communication->status == self::STATUS_DRAFT_DELETE) {
                        $status = self::STATUS_DRAFT_DELETE;
                    }
                    if ($communication->delete()) {
                        CacheHelper::forget(self::KEY_CACHE_WO, $input['project_id']);
                        $statusText = $arrayLablelStatus[$status];
                        View::insertProjectLogWO($input['project_id'], $statusText, $labelElement, null, null);
                        return true;
                    } 
                }
            }
        } else {
            $status = self::STATUS_DELETE;
            $communication = self::find($input['id']);
            if ($communication) {
                if ($communication->delete()) {
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
        $communicationDraft = self::where('project_id', $input['project_id']);
        if ($typeSubmit) {
            $communicationDraft = $communicationDraft->whereIn('status', [self::STATUS_DRAFT, self::STATUS_FEEDBACK]);
        } else {
            $communicationDraft = $communicationDraft->where('status', self::STATUS_DRAFT);
        }
        $communicationDraft = $communicationDraft->get();
        if(count($communicationDraft) > 0) {
            $title = Lang::get('project::view.Add object for Project communication');
            $content .= view('project::template.content-task', ['inputs' => $communicationDraft, 'title' => $title, 'type' => $typeWO])->render();
        }

        $communicationDraftEdit = self::where('project_id', $input['project_id']);
        if ($typeSubmit) {
            $communicationDraftEdit = $communicationDraftEdit->whereIn('status', [self::STATUS_DRAFT_EDIT, self::STATUS_FEEDBACK_EDIT]);
        } else {
            $communicationDraftEdit = $communicationDraftEdit->where('status', self::STATUS_DRAFT_EDIT);
        }
        $communicationDraftEdit = $communicationDraftEdit->get();
        if(count($communicationDraftEdit) > 0) {
            $title = Lang::get('project::view.Edit object for Project communication');
            $content .= view('project::template.content-task', ['inputs' => $communicationDraftEdit, 'title' => $title, 'type' => $typeWO])->render();
        }

        $communicationDelete = self::where('project_id', $input['project_id']);
        if ($typeSubmit) {
            $communicationDelete = $communicationDelete->whereIn('status', [self::STATUS_DRAFT_DELETE, self::STATUS_FEEDBACK_DELETE]);
        } else {
            $communicationDelete = $communicationDelete->where('status', self::STATUS_DRAFT_DELETE);
        }
        $communicationDelete = $communicationDelete->get();
        if(count($communicationDelete) > 0) {
            $title = Lang::get('project::view.Delete object for Project communication');
            $content .= view('project::template.content-task', ['inputs' => $communicationDelete, 'title' => $title, 'type' => $typeWO])->render();
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
        $communicationDraft = self::where('project_id', $input['project_id'])
                                    ->whereIn('status', [self::STATUS_DRAFT, self::STATUS_FEEDBACK])
                                    ->get();
        if(count($communicationDraft) > 0) {
            foreach($communicationDraft as $communication) {
                $communication->status = self::STATUS_SUBMITTED;
                $communication->task_id = $task->id;
                $communication->save();
            }
        }

        $communicationEdit = self::where('project_id', $input['project_id'])
                                        ->whereIn('status', [self::STATUS_DRAFT_EDIT, self::STATUS_FEEDBACK_EDIT])
                                        ->whereNotNull('parent_id')
                                        ->get();
        if(count($communicationEdit) > 0) {
            foreach($communicationEdit as $communication) {
                $communication->status = self::STATUS_SUBMIITED_EDIT;
                $communication->task_id = $task->id;
                $communication->save();
            }
        }

        $communicationDelete = self::where('project_id', $input['project_id'])
                                    ->whereIn('status', [self::STATUS_DRAFT_DELETE, self::STATUS_FEEDBACK_DELETE])
                                    ->get();
        if(count($communicationDelete)) {
            foreach($communicationDelete as $communication) {
                $communication->status = self::STATUS_SUBMMITED_DELETE;
                $communication->task_id = $task->id;
                $communication->save();
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
        $communicationDraft = self::where('project_id', $projectId);
        if ($statusTask == Task::STATUS_APPROVED) {
            if ($type == self::TYPE_ADD) {
                $communicationDraft = $communicationDraft->where('status', self::STATUS_REVIEWED);
            } else if($type == self::TYPE_EDIT) {
                $communicationDraft = $communicationDraft->where('status', self::STATUS_REVIEWED_EDIT);
            } else {
                $communicationDraft = $communicationDraft->where('status', self::STATUS_REVIEWED_DELETE);
            }
        } else if ($statusTask == Task::STATUS_FEEDBACK || $statusTask == Task::STATUS_REVIEWED) {
            if ($type == self::TYPE_ADD) {
                $communicationDraft = $communicationDraft->whereIn('status', [self::STATUS_SUBMITTED, self::STATUS_REVIEWED]);
            } else if($type == self::TYPE_EDIT) {
                $communicationDraft = $communicationDraft->whereIn('status', [self::STATUS_SUBMIITED_EDIT, self::STATUS_REVIEWED_EDIT]);
            } else {
                $communicationDraft = $communicationDraft->whereIn('status', [self::STATUS_SUBMMITED_DELETE, self::STATUS_REVIEWED_DELETE]);
            }
        }
        $communicationDraft = $communicationDraft->get();
        if(count($communicationDraft) > 0) {
            if ($statusTask == Task::STATUS_APPROVED) {
                if ($type == self::TYPE_DELETE) {
                    foreach($communicationDraft as $communication) {
                        $communication = self::find($communication->parent_id);
                        $communication->delete();
                        if($communicationParent) {
                            $communicationParent->delete();
                        }
                    }
                } else {
                    foreach($communicationDraft as $communication) {
                        $communicationParent = self::find($communication->parent_id);
                        if($communicationParent) {
                            $communication->parent_id = null;
                            $communication->task_id = null;
                            $communication->save();
                            $communicationParent->delete();
                        }
                        $communication->status = self::STATUS_APPROVED;
                        $communication->save();
                    }
                }
            } else if ($statusTask == Task::STATUS_REVIEWED) {
                foreach($communicationDraft as $communication) {
                    if ($communication->status == self::STATUS_SUBMITTED) {
                        $communication->status = self::STATUS_REVIEWED;
                    }
                    if ($communication->status == self::STATUS_SUBMIITED_EDIT) {
                        $communication->status = self::STATUS_REVIEWED_EDIT;
                    }
                    if ($communication->status == self::STATUS_SUBMMITED_DELETE) {
                        $communication->status = self::STATUS_REVIEWED_DELETE;
                    }
                    $communication->save();
                }
            } else if ($statusTask == Task::STATUS_FEEDBACK) {
                foreach($communicationDraft as $communication) {
                    if ($communication->status == self::STATUS_SUBMITTED ||
                        $communication->status == self::STATUS_REVIEWED) {
                        $communication->status = self::STATUS_FEEDBACK;
                    }
                    if ($communication->status == self::STATUS_SUBMIITED_EDIT ||
                        $communication->status == self::STATUS_REVIEWED_EDIT) {
                        $communication->status = self::STATUS_FEEDBACK_EDIT;
                    }
                    if ($communication->status == self::STATUS_SUBMMITED_DELETE ||
                        $communication->status == self::STATUS_REVIEWED_DELETE) {
                        $communication->status = self::STATUS_FEEDBACK_DELETE;
                    }
                    $communication->save();
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
        $allCommunication = self::getAllCommunication($project->id);
        return view('project::components.communication', ['permissionEdit' => $permissionEdit, 'checkEditWorkOrder' => $checkEditWorkOrder, 'allCommunication' => $allCommunication, 'detail' => true])->render();
    }
}